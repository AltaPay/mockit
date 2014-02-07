<?php

class MockitRecursiveMatcher
	extends MockitMatcher
{
	public function __construct(Mockit $mock, ReflectionClass $class)
	{
		parent::__construct($mock, $class);
	}
	
	public function __call($name, $arguments)
	{
		$this->event = new MockitEvent($this->mock,$name, $arguments, count($this->mock->getVerificationMatches()));
		
		foreach($this->mock->getRecursiveMocks() as $recursiveEvent) /* @var $recursiveEvent MockitRecursiveEvent */ 
		{
			if($this->event->matches($recursiveEvent->getEvent())->matches() || $recursiveEvent->getEvent()->matches($this->event)->matches())
			{
				return $recursiveEvent->getMock();
			}
		}

		$mock = $this->mock->getRecursiveMockForMethod($this->event);
		if(is_array($mock))
		{
			throw new Exception('Recursive mocks does not work methods that returns an array, which this method does:  '. $this->event->getMock()->getClassname().'->'.$this->event->getName().'()');
		}
		if(is_null($mock))
		{
			throw new Exception('Recursive mocks does not work on methods without a defined mockable return type, which this method does: '. $this->event->getMock()->getClassname().'->'.$this->event->getName().'()');
		}
		return $mock;
	}
}