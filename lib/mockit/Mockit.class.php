<?php

class Mockit
{
	/**
	* @var ReflectionClass
	*/
	private $class;

	private $mockitor;
	
	static private $events;
	static private $verificationMatches;
	private $matchers = array();
	private $outOfOrder = false;
	
	private static $mockitors = array();
	
	public function __construct($classname, $uniqueId=null)
	{
		$this->class = new ReflectionClass($classname);
		$this->mockitor = $this->getMockitor($this->class);
		if(!is_null($uniqueId))
		{
			$this->mockitor->__oid__ = $uniqueId;
		}
		self::$events = array();
		self::$verificationMatches = array();
	}
	
	public function outOfOrder()
	{
		$this->outOfOrder = true;
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
		return new MockitVerifier($this, $this->class, null);
	}
	
	public function exactly($times)
	{
		return new MockitVerifier($this, $this->class, $times);
	}
	
	public function once()
	{
		return new MockitVerifier($this, $this->class, 1);
	}
	
	public function never()
	{
		return new MockitVerifier($this, $this->class, 0);
	}
	
	public function instance()
	{
		return $this->mockitor;
	}
	
	public function getEvents()
	{
		return self::$events;
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
				$tmpl .= 'public function '.$method->name.'(';
				$args = array();
				$classlessArgs = array();
				foreach($method->getParameters() as $parameter) /* @var $parameter ReflectionParameter */
				{
					if(!is_null($parameter->getClass()))
					{
						$args[] = $parameter->getClass()->getName().' $'.$parameter->getName();
					}
					else
					{
						$args[] = '$'.$parameter->getName();
					}
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
}