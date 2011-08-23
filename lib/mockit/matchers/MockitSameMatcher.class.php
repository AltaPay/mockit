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
	
	function matchDescription($other)
	{
		if($this->matches($other))
		{
			return Mockit::uniqueid($this->obj) .' === '.Mockit::uniqueid($other);
		}
		else
		{
			return Mockit::uniqueid($this->obj) .' !== '.Mockit::uniqueid($other);
		}
	}
	
	public function description()
	{
		return Mockit::describeArgument($this->value);
	}

}