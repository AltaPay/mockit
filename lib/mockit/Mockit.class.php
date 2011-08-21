<?php

class Mockit
{
	/**
	* @var ReflectionClass
	*/
	private $class;

	private $mockitor;
	
	private $events = array();
	private $matchers = array();
	
	/**
	 * @var MockitInOrder
	 */
	private $inOrder;
	
	private static $mockitors = array();
	
	public function __construct($classname)
	{
		$this->class = new ReflectionClass($classname);
		$this->mockitor = $this->getMockitor($this->class);
	}
	
	public function setInOrder(MockitInOrder $inOrder)
	{
		$this->inOrder = $inOrder;
	}
	
	/**
	 * @return MockitInOrder
	 */
	public function getInOrder()
	{
		return $this->inOrder;
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
		if(!is_null($this->inOrder))
		{
			return $this->inOrder->getEvents();
		}
		else
		{
			return $this->events;
		}
	}
	
	public function process(MockitEvent $event)
	{
		$this->events[] = $event;
		if(!is_null($this->inOrder))
		{
			$this->inOrder->addEvent($event);
		}
		foreach($this->matchers as $matcher) /* @var $matcher MockitMatcher */
		{
			if($matcher->_getEvent()->matches($event)->matches())
			{
				return $matcher->_getStub()->_executeStub();
			}
		}
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
				$tmpl .= "\t".'return $this->mock->process(new MockitEvent($this->mock, "'.$method->getName().'", array('.implode(',',$classlessArgs).')));'."\n";
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