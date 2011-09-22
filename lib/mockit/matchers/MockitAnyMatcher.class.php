<?php

class MockitAnyMatcher
	implements IMockitMatcher
{
	public function matches($other)
	{
		return true;
	}

	function matchDescription($other)
	{
		if(is_object($other))
		{
			$other = Mockit::uniqueid($other);
		}
		return $other.' matches anything';
	}
	
	public function description()
	{
		return '*anything*';
	}
}