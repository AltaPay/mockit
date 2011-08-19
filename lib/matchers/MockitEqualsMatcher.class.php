<?php

class MockitEqualsMatcher
	implements IMockitMatcher
{
	private $value;
	
	public function __construct($value)
	{
		$this->value = $value;
	}
	
	public function matches($other)
	{
		return $this->value == $other;
	}
}