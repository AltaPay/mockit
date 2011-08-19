<?php

class MockitSameMatcher
	implements IMockitMatcher
{
	private $obj;
	
	public function __construct($obj)
	{
		$this->obj = $obj;
	}
	
	public function matches($other)
	{
		return $this->obj === $other;
	}
}