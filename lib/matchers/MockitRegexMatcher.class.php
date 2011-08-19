<?php

class MockitRegexMatcher
	implements IMockitMatcher
{
	private $regex;
	
	public function __construct($regex)
	{
		$this->regex = $regex;
	}
	
	public function matches($other)
	{
		return preg_match($this->regex, $other);
	}
}