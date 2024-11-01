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
	
	function matchDescription($other)
	{
		if($this->matches($other))
		{
			return $this->regex .' did not match "'.$other.'"';
		}
		else
		{
			return $this->regex .' matched "'.$other.'"';
		}
	}
	
	public function description()
	{
		return $this->regex;
	}
}