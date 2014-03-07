<?php

class MockitParameterMatchResult
{
	private $matches;
	/**
	 * @var IMockitMatcher
	 */
	private $matcher;
	private $actual;
	private $matchScore = 0;
	
	public function __construct(ReflectionParameter $reflectionParameter=null,$expected, $actual)
	{
		if(!($expected instanceof IMockitMatcher))
		{
			if(is_object($expected) || ($reflectionParameter != null && $reflectionParameter->getClass() != null))
			{
				$expected = new MockitSameMatcher($expected);
			}
			else
			{
				$expected = new MockitEqualsMatcher($expected);
			}
		}
		/* @var $expected IMockitMatcher */
		$this->matches = $expected->matches($actual);
		if($this->matches && ($expected instanceof MockitAnyMatcher))
		{
			$this->matchScore = 0.9;
		}
		else if($this->matches)
		{
			$this->matchScore = 1;
		}
		$this->matcher = $expected;
		$this->actual = $actual;
	}
	
	public function matches()
	{
		return $this->matches;
	}

	public function getMatchScore()
	{
		return $this->matchScore;
	}

	public function matchDescription()
	{
		return $this->matcher->matchDescription($this->actual);
	}

}