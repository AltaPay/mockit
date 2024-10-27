<?php

class MockitTypeMatcher
	implements IMockitMatcher
{
	private $type;

	public function __construct($type)
	{
		$this->type = $type;
	}

	public function matches($other)
	{
		return $other instanceof $this->type;
	}

	function matchDescription($other)
	{
		if($this->matches($other))
		{
			return Mockit::uniqueid($other).' is of type '.$this->type;
		}
		else
		{
			return Mockit::uniqueid($other).' is NOT of type '.$this->type;
		}
	}

	public function description()
	{
		return Mockit::describeArgument($this->value);
	}

}