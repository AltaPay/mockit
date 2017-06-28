<?php

class Mockit
	implements IMockit
{
	/**
	* @var ReflectionClass
	*/
	private $class;

	private $mockitor;
	private $isSpy = false;

	/**
	 * @var MockitEventList
	 */
	static private $events = null;
	/**
	 * @var MockitEventList
	 */
	static private $unmatchedEvents = null;
	/**
	 * @var MockitVerificationMatchList
	 */
	static private $verificationMatches = null;
	static private $recursiveMocks = array();
	private $matchers = array();
	private $outOfOrder = false;
	private $recursive = false;
	private $dynamic = false;
	
	public function __construct($classname, $uniqueId=null)
	{
		if(is_null(self::$verificationMatches))
		{
			self::$verificationMatches = new MockitVerificationMatchList();
		}
		if(is_null(self::$events))
		{
			self::$events = new MockitEventList();
		}

		$this->class = new ReflectionClass($classname);
		if(is_object($classname))
		{
			$this->mockitor = MockitGenerator::getSpy($this->class, $classname, $this);
			$this->isSpy = true;
		}
		else
		{
			$this->mockitor = MockitGenerator::getMockitor($this->class,$this);
		}
		
		if(!is_null($uniqueId))
		{
			$this->mockitor->__oid__ = $uniqueId;
		}
		else
		{
			$this->mockitor->__oid__ = $this->generateUniqueId();
		}
	}

	public function getReflectionClass()
	{
		return $this->class;
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
		self::$events = new MockitEventList();
		self::$verificationMatches = new MockitVerificationMatchList();
		self::$recursiveMocks = array();
		self::$unmatchedEvents = null;
	}
	
	public function outOfOrder()
	{
		$this->outOfOrder = true;
		return $this;
	}
	
	public function recursive()
	{
		if($this->isSpy)
		{
			throw new Exception('Cannot make spy recursive');
		}
		$this->recursive = true;
		return $this;
	}
	
	public function dynamic()
	{
		if(is_null($this->class->getMethod('__call')))
		{
			throw new Exception('Dynamic mocks can only be made for objects that have a __call method.');
		}
		
		$this->dynamic= true;
		return $this;
	}


	/**
	 * @return MockitEventList
	 */
	public function getUnmatchedEvents()
	{
		if(is_null(self::$unmatchedEvents))
		{ 
			self::$unmatchedEvents = self::$events->copy();
		}
		return self::$unmatchedEvents;
	}
	
	/**
	 * @return MockitEvent
	 */
	public function nextUnmatchedEvent()
	{
		self::getUnmatchedEvents();
		if(self::$unmatchedEvents->isEmpty())
		{
			return null;
		}
		return self::$unmatchedEvents->first();
	}
	
	public static function printUnmatchedEvents()
	{
		print "Umatched events: \n";
		foreach(self::$unmatchedEvents->getIterator() as $event)
		{
			print $event->eventDescription()."\n";
		}
	} 
	
	/**
	 * @return MockitEvent
	 */
	public function shiftUnmatchedEvents()
	{
		self::getUnmatchedEvents();
		return self::$unmatchedEvents->shift();
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
		return new MockitVerifier($this, MockitExpectedCount::get(null));
	}
	
	public function exactly($times)
	{
		return new MockitVerifier($this, MockitExpectedCount::get($times));
	}
	
	public function once()
	{
		return new MockitVerifier($this, MockitExpectedCount::get(1));
	}
	
	public function never()
	{
		return new MockitVerifier($this, MockitExpectedCount::get(0));
	}

	public function invoked()
	{
		if($this->outOfOrder)
		{
			throw new Exception('invoked only makes sense for in order mocks');
		}
		return new MockitVerifier($this, MockitExpectedCount::invoked());
	}

	public function instance()
	{
		return $this->mockitor;
	}

	/**
	 * @return MockitEventList
	 */
	public function getEvents()
	{
		return self::$events;
	}
	
	public function with()
	{
		return new MockitRecursiveMatcher($this,$this->class);
	}

	public function noFurtherInvocations()
	{
		foreach(self::getUnmatchedEvents() as $event)
		{
			if($event->getMock() === $this)
			{
				throw new MockitVerificationException('Unexpected invocation on mock: '.$event->eventDescription());
			}
		}
	}
	
	public function haveStubEvent(MockitEvent $event)
	{
		foreach($this->matchers as $matcher) /* @var $matcher MockitMatcher */
		{
			if($matcher->_getEvent()->matches($event)->matches())
			{
				return true;
			}
		}
		return false;
	}
	
	public function process(MockitEvent $event)
	{
		if($this->dynamic && $event->getName() == '__call')
		{
			$arguments = $event->getArguments();
			$event = new MockitEvent($event->getMock(), array_shift($arguments), $arguments, $event->getIndex());
		}
		self::$events->add($event);
		foreach($this->matchers as $matcher) /* @var $matcher MockitMatcher */
		{
			if($matcher->_getEvent()->matches($event)->matches())
			{
				return $matcher->_getStub($event->getName())->_executeStub($event->getArguments());
			}
		}
		if($this->recursive)
		{
			$bestMatch = null; /* @var $bestMatch MockitMatchResult */
			$bestMatchRecursiveEvent = null;
			foreach($this->getRecursiveMocks() as $recursiveEvent) /* @var $recursiveEvent MockitRecursiveEvent */ 
			{ 
				if($this !== $recursiveEvent->getEvent()->getMock())
				{
					continue;
				}
				$m = $recursiveEvent->getEvent()->matches($event);
				if($m->matches())
				{
					if(is_null($bestMatch) || $bestMatch->getMatchScore() < $m->getMatchScore())
					{
						$bestMatchRecursiveEvent = $recursiveEvent;
						$bestMatch = $m;
					}
				}
			}
			if(!is_null($bestMatch))
			{
				//print 'Real best match: '.$bestMatch->matchDescription().': '.$bestMatch->getMatchScore()."\n";
				//print "found match for recursive mock: ".Mockit::uniqueid($bestMatchRecursiveEvent->getMock()->instance())."\n";
				return $bestMatchRecursiveEvent->getMock()->instance();
			}
			
			$mock = $this->getRecursiveMockForMethod($event);
			//print 'Real creating new mock: '.Mockit::uniqueid($mock->instance())."\n";
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
		$returnClass = MockitGenerator::getReturnTypeFor($this->class, $event->getName(), $this->isDynamic());

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
	
	public function getClassname()
	{
		return $this->class->getName();
	}
	
	/**
	 * @return MockitMatchResult
	 */
	public function getPreviousVerificationMatch()
	{
		if(self::$verificationMatches->count() == 0)
		{
			return null;
		}
		return self::$verificationMatches->last();
	}

	/**
	 * @return MockitVerificationMatchList
	 */
	public static function getVerificationMatches()
	{
		return self::$verificationMatches;
	}
	
	public function getRecursiveMocks()
	{
		return self::$recursiveMocks;
	}
	
	public function addVerificationMatch(MockitMatchResult $verificationEvent)
	{
		self::$verificationMatches->add($verificationEvent);
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


	

	public function isRecursive()
	{
		return $this->recursive;
	}

	public function isDynamic()
	{
		return $this->dynamic;
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

	public static function initMocks($testClass)
	{
		$reflectClass = new ReflectionClass($testClass);

		$initializedMockObjects = array();
		$initializedMockObjectsByType = array();
		foreach($reflectClass->getProperties() as $property) /* @var $property ReflectionProperty */
		{
			$docComment = $property->getDocComment();
			if(preg_match('/\@var\s+Mock_([^\s;]+)(?:\s(.+))?\n/',$docComment, $matches))
			{
				$property->setAccessible(true);
				$uniqueId = null;
				if(isset($matches[2]) && preg_match('/#([\S]+)/', $matches[2],$idMatches))
				{
					$uniqueId = $idMatches[1];
				}
				$mockit = new Mockit($matches[1], $uniqueId);
				if(isset($matches[2]))
				{
					if(strpos($matches[2],"recursive") !== false)
					{
						$mockit->recursive();
					}
					if(strpos($matches[2],"dynamic") !== false)
					{
						$mockit->dynamic();
					}
					if(strpos($matches[2],"outOfOrder") !== false)
					{
						$mockit->outOfOrder();
					}
				}

				$initializedMockObjects[$property->getName()] = $mockit;
				$initializedMockObjectsByType[$matches[1]] = $mockit;
				$property->setValue($testClass, $mockit);
			}
		}

		foreach($reflectClass->getProperties() as $property) /* @var $property ReflectionProperty */
		{
			$docComment = $property->getDocComment();
			if(preg_match('/\@AutoInitialize/',$docComment, $matches) && preg_match('/\@var\s+/',$docComment))
			{
				if(!preg_match('/\@var\s+(\S+)?\n/',$docComment, $typeMatches))
				{
					throw new Exception('Could not auto initialize: '.$property->getName().'. Invalid variable type.');
				}

				$property->setAccessible(true);
				$type = $typeMatches[1];
				$initClass = new ReflectionClass($type);
				if(!is_null($initClass->getConstructor()))
				{
					$parameters = array();
					foreach($initClass->getConstructor()->getParameters() as $parameter)
					{
						$parameters[] = self::getAutoInitializeMockParam($initializedMockObjects, $initializedMockObjectsByType, $reflectClass, $testClass, $parameter);
					}

					$autoInstance = $initClass->newInstanceArgs($parameters);
				}
				else
				{
					// Please note that if the constructor is on the parent class (which works in PHP) this will fail
					$autoInstance = $initClass->newInstance();
				}

				// Find @Inject's in the class hierarchy
				do
				{
					foreach($initClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
					{
						if(preg_match('/\@Inject/',$method->getDocComment()))
						{
							$parameters = array();
							foreach($method->getParameters() as $parameter)
							{
								$parameters[] = self::getAutoInitializeMockParam($initializedMockObjects, $initializedMockObjectsByType, $reflectClass, $testClass, $parameter);
							}

							$method->invokeArgs($autoInstance, $parameters);
						}
					}

					$initClass = $initClass->getParentClass();
				}
				while(is_object($initClass));

				$property->setValue($testClass, $autoInstance);
			}
		}
	}

	private static function getAutoInitializeMockParam(array $initializedMockObjects, array $initializedMockObjectsByType, ReflectionClass $reflectClass, $testClass, $parameter)
	{
		if(isset($initializedMockObjects[$parameter->getName()]))
		{
			return $initializedMockObjects[$parameter->getName()]->instance();
		}
		else if(isset($initializedMockObjectsByType[$parameter->getClass()->getName()]))
		{
			throw new Exception('You already initialized a mock of type: '.$parameter->getClass()->getName().' but it did not have the expected name: '.$parameter->getName());
		}
		else if ($reflectClass->hasProperty($parameter->getName()))
		{
			$nonMockProperty = $reflectClass->getProperty($parameter->getName());
			$nonMockProperty->setAccessible(true);
			return $nonMockProperty->getValue($testClass);
		}
		else
		{
			$mock = new Mockit($parameter->getClass()->getName(), 'AutoInitialized_'.$parameter->getClass()->getName());

			return $mock->instance();
		}
	}
}