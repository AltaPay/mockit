<?php

class MockitParameterMatchResult
{
	private $matches;
	/**
	 * @var IMockitMatcher
	 */
	private $matcher;
	private $actual;
	
	public function __construct($expected, $actual)
	{
		if(!($expected instanceof IMockitMatcher))
		{
			if(is_object($expected))
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
		$this->matcher = $expected;
		$this->actual = $actual;
	}
	
	public function matches()
	{
		return $this->matches;
	}
	
	public function matchDescription()
	{
		return $this->matcher->matchDescription($this->actual);
	}
}