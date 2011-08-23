<?php

class MockitVerifier
	extends MockitMatcher
{
	private $expectedCount;
	
	public function __construct(Mockit $mock,$expectedCount)
	{
		parent::__construct($mock);
		$this->expectedCount = $expectedCount;
	}
	
	public function __call($name, $arguments)
	{
		$this->event = new MockitEvent($this->mock,$name, $arguments, count($this->mock->getVerificationMatches()));
		
		$foundCount = 0;
		$methodFoundCount = 0;
		$methodMatchResults = array();
		$matchResults = array();
		foreach($this->mock->getEvents() as $event) /* @var $event MockitEvent */
		{
			if($this->mock !== $event->getMock())
			{
				continue;
			}
				
			$matchResult = $this->event->matches($event);
			if($matchResult->matches())
			{
				$foundCount++;
				$matchResults[] = $matchResult;
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
		if(!(is_null($this->expectedCount) && $methodFoundCount == $foundCount) && $foundCount !== $this->expectedCount)
		{
			$this->throwException($foundCount,$methodFoundCount, $methodMatchResults);
		}
		else
		{
			if(!$this->mock->getOutOfOrder())
			{
				$lastMatch = $this->mock->getLastVerificationMatch(); /* @var $lastMatch MockitMatchResult */
				$actualMatchResult = $this->getRelevantMatchResult($matchResults);
				
				if(!is_null($lastMatch))
				{
					if($lastMatch->getMatchedEvent()->getIndex() >= $actualMatchResult->getMatchedEvent()->getIndex())
					{
						throw new MockitOutOfOrderException($lastMatch, $actualMatchResult);
					}
				}
				$this->mock->addVerificationMatch($actualMatchResult);
			}
		}
	}
	
	/**
	 * @return MockitMatchResult
	 */
	private function  getRelevantMatchResult($matchResults)
	{
		$index = 0;
		foreach($this->mock->getVerificationMatches() as $verificationMatch) /* @var $verificationMatch MockitMatchResult */
		{
			foreach($matchResults as $matchResult) /* @var $matchResult MockitMatchResult */
			{
				if($verificationMatch->getMatchedEvent() === $matchResult->getMatchedEvent())
				{
					$index++;
					break;
				}
			}
			
		}
		
		return $matchResults[$index];
	}
	
	private function throwException($foundCount, $methodFoundCount, $methodMatchResults)
	{
		if(is_null($this->expectedCount))
		{
			throw new MockitVerificationException('Method expected to be called any number of times, but with the correct arguments. Argument matches were: '."\n".$this->getArgumentDescriptions($methodMatchResults));
		}
		if($methodFoundCount == 0)
		{
			throw new MockitVerificationException('Method was expected to be called '.$this->expectedCount.' times, but was called '.$methodFoundCount.' times');
		}
		else if($methodFoundCount == $this->expectedCount && $foundCount != $this->expectedCount)
		{
			throw new MockitVerificationException('Method was called the correct number of times, but with incorrect parameters: '."\n".$this->getArgumentDescriptions($methodMatchResults));
		}
		else if($foundCount == $methodFoundCount)
		{
			throw new MockitVerificationException('Method was expected to be called with the correct arguments '.$this->expectedCount.' times, but was called '.$foundCount.' times with the correct arguments');
		}
		else
		{
			throw new MockitVerificationException('Method was expected to be called with the correct arguments '.$this->expectedCount.' times, but was called '.$methodFoundCount.' times but only '.$foundCount.' with the correct arguments. Incorrect matches were: '."\n".$this->getArgumentDescriptions($methodMatchResults));
		}
	}
	
	private function getArgumentDescriptions($methodMatchResults)
	{
		$result = array();
		foreach($methodMatchResults as $invocationCount => $methodMatch) /* @var $methodMatch MockitMatchResult */
		{
			$result[] = 'At invocation count '.$invocationCount.' argument matches were: '.$methodMatch->matchDescription();
		}
		return implode("\n",$result);
	}
}
