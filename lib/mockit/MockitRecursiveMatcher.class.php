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

		$bestMatch = null; /* @var $bestMatch MockitMatchResult */
		$bestMatchMock = null;
		foreach($this->mock->getRecursiveMocks() as $recursiveEvent) /* @var $recursiveEvent MockitRecursiveEvent */
		{
			$m = $recursiveEvent->getEvent()->matches($this->event);
			$m2 = $this->event->matches($recursiveEvent->getEvent());
			if($m->matches() && $m2->matches() && ($m->getMatchScore() == $m2->getMatchScore()))
			{
				if(is_null($bestMatch) || $bestMatch->getMatchScore() < $m->getMatchScore())
				{
					$bestMatch = $m;
					$bestMatchMock = $recursiveEvent->getMock();
				}
				if(is_null($bestMatch) || $bestMatch->getMatchScore() < $m2->getMatchScore())
				{
					$bestMatch = $m2;
					$bestMatchMock = $this->event->getMock();
				}
			}

		}
		if(!is_null($bestMatch))
		{
			return $bestMatchMock;
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