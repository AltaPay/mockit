<?php

class MockitVerificationMatchList
	implements IteratorAggregate
{
	private $matches = array();

	public function count()
	{
		return count($this->matches);
	}

	public function last()
	{
		return $this->matches[count($this->matches)-1];
	}

	public function add(MockitMatchResult $verificationEvent)
	{
		$this->matches[] = $verificationEvent;
	}

	/**
	 * @return MockitMatchResult[]
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->matches);
	}

	public function matchesAny(MockitEvent $event)
	{
		foreach($this->getIterator() as $verificationMatch)
		{
			if($verificationMatch->getVerificationEvent()->matches($event)->matches())
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $matchResults MockitMatchResult[]
	 * @return MockitMatchResult
	 */
	public function getRelevantMatchResult($matchResults, MockitMatchResult $lastMatch = null)
	{
		$index = 0;
		$found = false;
		foreach($this->getIterator() as $verificationMatch)
		{
			foreach($matchResults as $matchResult)
			{
				if($verificationMatch->getMatchedEvent() === $matchResult->getMatchedEvent())
				{
					$found = true;
					$index++;
					break;
				}
			}
		}

		if(!$found && !is_null($lastMatch))
		{
			foreach($matchResults as $matchResult) /* @var $matchResult MockitMatchResult */
			{
				if($matchResult->getMatchedEvent()->getIndex() > $lastMatch->getMatchedEvent()->getIndex())
				{
					return $matchResult;
				}
			}

		}
		return @$matchResults[$index];
	}
}