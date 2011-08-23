<?php

class MockitRecursiveMatcher
	extends MockitMatcher
{
	public function __construct(Mockit $mock)
	{
		parent::__construct($mock);
	}
	
	public function __call($name, $arguments)
	{
		$this->event = new MockitEvent($this->mock,$name, $arguments, count($this->mock->getVerificationMatches()));
		
		foreach($this->mock->getRecursiveMocks() as $recursiveEvent) /* @var $recursiveEvent MockitRecursiveEvent */ 
		{ 
			if($this->mock !== $recursiveEvent->getEvent()->getMock())
			{
				continue;
			}
			
			if($this->event->getArguments() == $recursiveEvent->getEvent()->getArguments())
			{
				return $recursiveEvent->getMock();
			}
		}
		
		$mock = $this->mock->getRecursiveMockForMethod($this->event);
		if(is_array($mock))
		{
			throw new Exception('Recursive mocks does not work methods that returns an array');
		}
		if(is_null($mock))
		{
			throw new Exception('Recursive mocks does not work on methods without a defined mockable return type');
		}
		return $mock;
	}
}