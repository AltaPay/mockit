<?php

class Mockit
	implements IMockit
{
	/**
	* @var ReflectionClass
	*/
	private $class;

	private $mockitor;
	
	static private $events;
	static private $verificationMatches;
	static private $recursiveMocks;
	private $matchers = array();
	private $outOfOrder = false;
	private $recursive = false;
	
	private static $mockitors = array();
	
	public function __construct($classname, $uniqueId=null)
	{
		$this->class = new ReflectionClass($classname);
		$this->mockitor = $this->getMockitor($this->class);
		if(!is_null($uniqueId))
		{
			$this->mockitor->__oid__ = $uniqueId;
		}
		else
		{
			$this->mockitor->__oid__ = $this->generateUniqueId();
			
		}
	}
	
	private function generateUniqueId()
	{
		$ex = new Exception();
		$trace = $ex->getTrace();

		foreach($trace as $index => $traceLine)
		{
			if($traceLine['function'] == 'getMockit' && $traceLine['class'] == 'MockitTestCase')
			{
				return $trace[$index + 1]['class'].'['.$traceLine['line'].']'.'_'.$this->class->getName();
			}
			
			if($traceLine['function'] == 'getRecursiveMockForMethod' && $traceLine['class'] == 'Mockit')
			{
				return Mockit::uniqueid($traceLine['args'][0]->getMock()->instance()).'->'.$traceLine['args'][0]->getName().'('.$traceLine['args'][0]->getArgumentsAsString().')';
				//return $trace[$index + 1]['class'].'['.$traceLine['line'].']'.'_'.$this->class->getName();
			}
		}
		return Mockit::uniqueid($this->instance());
	}
	
	public static function resetMocks()
	{
		self::$events = array();
		self::$verificationMatches = array();
		self::$recursiveMocks = array();
	}
	
	public function outOfOrder()
	{
		$this->outOfOrder = true;
		return $this;
	}
	
	public function recursive()
	{
		$this->recursive = true;
		return $this;
	}
	
	public function getOutOfOrder()
	{
		return $this->outOfOrder;
	}
	
	public function when()
	{
		$matcher = new MockitMatcher($this, $this->class);
		array_unshift($this->matchers, $matcher);
		return $matcher;
	}
	
	public function any()
	{
		return new MockitVerifier($this, null);
	}
	
	public function exactly($times)
	{
		return new MockitVerifier($this, $times);
	}
	
	public function once()
	{
		return new MockitVerifier($this, 1);
	}
	
	public function never()
	{
		return new MockitVerifier($this, 0);
	}
	
	public function instance()
	{
		return $this->mockitor;
	}
	
	public function getEvents()
	{
		return self::$events;
	}
	
	public function with()
	{
		return new MockitRecursiveMatcher($this,$this->class);
	}
	
	public function process(MockitEvent $event)
	{
		self::$events[] = $event;
		foreach($this->matchers as $matcher) /* @var $matcher MockitMatcher */
		{
			if($matcher->_getEvent()->matches($event)->matches())
			{
				return $matcher->_getStub()->_executeStub();
			}
		}
		if($this->recursive)
		{
			
			foreach($this->getRecursiveMocks() as $recursiveEvent) /* @var $recursiveEvent MockitRecursiveEvent */ 
			{ 
				if($this !== $recursiveEvent->getEvent()->getMock())
				{
					continue;
				}
				if($recursiveEvent->getEvent()->matches($event)->matches())
				{
					return $recursiveEvent->getMock()->instance();
				}
			}
			
			$mock = $this->getRecursiveMockForMethod($event);
			if(is_array($mock))
			{
				return $mock;
			}
			if(!is_null($mock))
			{
				return $mock->instance();
			}
		}
		return null;
	}
	
	/**
	 * @return Mockit
	 */
	public function getRecursiveMockForMethod(MockitEvent $event)
	{
		$reflectionMethod = $this->class->getMethod($event->getName());

		if(preg_match('/\@return\s+([^\s]+)/',$reflectionMethod->getDocComment(),$matches))
		{
			$returnClass = $matches[1];
			
			if($returnClass == 'array')
			{
				return array();
			}
			try
			{
				
				if(class_exists($returnClass) || interface_exists($returnClass))
				{
					$mock = new Mockit($returnClass);
					$mock = $mock->recursive();
					array_unshift(self::$recursiveMocks, new MockitRecursiveEvent($event, $mock));
					return $mock;
				}
			}
			catch(Exception $exception)
			{
				// if class could not be loaded, just dont return a mock
				return null;
			}
		}
	}
	
	public function getClassname()
	{
		return $this->class->getName();
	}
	
	/**
	 * @return MockitMatchResult
	 */
	public function getLastVerificationMatch()
	{
		if(count(self::$verificationMatches) == 0)
		{
			return null;
		}
		return self::$verificationMatches[count(self::$verificationMatches)-1];
	}
	
	public function getVerificationMatches()
	{
		return self::$verificationMatches;
	}
	
	public function getRecursiveMocks()
	{
		return self::$recursiveMocks;
	}
	
	public function addVerificationMatch(MockitMatchResult $verificationEvent)
	{
		self::$verificationMatches[] = $verificationEvent;
	}
	
	private function getMockitor(ReflectionClass $class)
	{
		$mockitorClassname = 'Mockitor_'.$class->name;
		if(!isset(self::$mockitors[$mockitorClassname]))
		{
			if($class->isInterface())
			{
				$tmpl = 'class '.$mockitorClassname.' implements '.$class->name.'{';
			}
			else
			{
				$tmpl = 'class Mockitor_'.$class->name.' extends '.$class->name.'{';
			}
			$tmpl .= "\n";
			$tmpl .= 'private $mock;'."\n";
			$tmpl .= 'public function __construct(Mockit $mock) { $this->mock = $mock; }'."\n";
			foreach($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) /* @var $method ReflectionMethod */
			{
				if($method->name == '__construct' || $method->isStatic())
				{
					continue;
				}
				
				$tmpl .= 'public function '.$method->name.'(';
				$args = array();
				$classlessArgs = array();
				foreach($method->getParameters() as $parameter) /* @var $parameter ReflectionParameter */
				{
					$paramString = '';
					
					if($parameter->isArray())
					{
						$paramString .= 'array ';
					}
					else if(!is_null($parameter->getClass()))
					{
						$paramString .= $parameter->getClass()->getName().' ';
					}
					
					$paramString .= '$'.$parameter->getName();
					
					if($parameter->isDefaultValueAvailable())
					{
						switch(gettype($parameter->getDefaultValue()))
						{
							case 'boolean':
								$paramString .= ' = '.($parameter->getDefaultValue() ? 'true' : 'false');
								break;
							case 'integer':
							case 'double':
								$paramString .= ' = '.$parameter->getDefaultValue();
								break;
							case 'string':
								$paramString .= " = '".$parameter->getDefaultValue()."'";
								break;
							case 'NULL':
								$paramString .= ' = null';
								break;
							case 'array':
								$paramString .= ' = array()';
								break;
							case 'object':
								throw new Exception('default value with object. what to do!?');
							case 'resource':
								throw new Exception('default value with resource. what to do!?');
							
						}
						
					}
					$args[] = $paramString;
					$classlessArgs[] = '$'.$parameter->getName();
				}
				$tmpl .= implode(',',$args);
				$tmpl .= ')'."\n";
				$tmpl .= '{'."\n";
				$tmpl .= "\t".'return $this->mock->process(new MockitEvent($this->mock, "'.$method->getName().'", array('.implode(',',$classlessArgs).'),count($this->mock->getEvents())));'."\n";
				$tmpl .= '}'."\n";
			}
			$tmpl .= '}';

			eval($tmpl);
			
			self::$mockitors[$mockitorClassname] = true;
		}
		return new $mockitorClassname($this);
	}
	
	public static function uniqueid($object)
	{
		if(is_null($object))
		{
			return null;
		}
		
		if (!is_object($object))
		{
			throw new Exception("Same matcher only works for objects");
		}
	
		if (!isset($object->__oid__))
		{
			$object->__oid__ = uniqid(get_class($object).'_');
		}
		return $object->__oid__;
	}
	
	public static function describeArgument($argument)
	{
		if($argument instanceof IMockitMatcher)
		{
			return $argument->description();
		}
		else if(is_object($argument))
		{
			return Mockit::uniqueid($argument);
		}
		else if(is_array($argument))
		{
			return 'Array';
		}
		else
		{
			return $argument;
		}
	}
}