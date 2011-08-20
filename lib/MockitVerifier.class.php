<?php

class MockitVerifier
	extends MockitMatcher
{
	private $expectedCount;
	
	public function __construct(Mockit $mock,ReflectionClass $class,$expectedCount)
	{
		parent::__construct($mock, $class);
		$this->expectedCount = $expectedCount;
	}
	
	public function __call($name, $arguments)
	{
		$this->event = new MockitEvent($name, $arguments);
		$foundCount = 0;
		$methodFoundCount = 0;
		$methodMatchResults = array();
		foreach($this->mock->getEvents() as $event) /* @var $event MockitEvent */
		{
			$matchResult = $this->event->matches($event);
			if($matchResult->matches())
			{
				$foundCount++;
			}
			if($matchResult->methodMatches())
			{
				$methodFoundCount++;
			}
			if($matchResult->methodMatches() && !$matchResult->matches())
			{
				$methodMatchResults[$methodFoundCount] = $matchResult;
			}
			
		}
		if($foundCount != $this->expectedCount)
		{
			$this->throwException($foundCount,$methodFoundCount, $methodMatchResults);
		}
	}
	
	
	private function throwException($foundCount, $methodFoundCount, $methodMatchResults)
	{
		if($methodFoundCount == 0)
		{
			throw new MockitVerificationException($this->event->getName().' was expected to be called '.$this->expectedCount.' times, but was called '.$methodFoundCount.' times');
		}
		else if($methodFoundCount == $this->expectedCount && $foundCount != $this->expectedCount)
		{
			throw new MockitVerificationException($this->event->getName().' was called the correct number of times, but with incorrect parameters: '."\n".$this->getArgumentDescriptions($methodMatchResults));
		}
		else if($foundCount == $methodFoundCount)
		{
			throw new MockitVerificationException($this->event->getName().' was expected to be called with the correct arguments '.$this->expectedCount.' times, but was called '.$foundCount.' times with the correct arguments');
		}
		else
		{
			throw new MockitVerificationException($this->event->getName().' was expected to be called with the correct arguments '.$this->expectedCount.' times, but was called '.$methodFoundCount.' times but only '.$foundCount.' with the correct arguments. Incorrect matches were: '."\n".$this->getArgumentDescriptions($methodMatchResults));
		}
	}
	
	private function getArgumentDescriptions($methodMatchResults)
	{
		$result = array();
		foreach($methodMatchResults as $invocationCount => $methodMatch) /* @var $methodMatch MockitMatchResult */
		{
			$result[] = 'At invocation count '.$invocationCount.' argument matches were: '.$methodMatch->argumentMatchDescription();
		}
		return implode("\n",$result);
	}
}
