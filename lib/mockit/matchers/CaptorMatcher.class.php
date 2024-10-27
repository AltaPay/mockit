<?php

class CaptorMatcher
	implements IMockitMatcher
{
	private $other;
	
	public function getOther()
	{
		return $this->other;
	}
	
	public function matches($other)
	{
		$this->other = $other;
		return true;
	}

	function matchDescription($other)
	{
		return (is_object($other) ? get_class($other) : $other).' matches anything';
	}
	
	public function description()
	{
		return '*anything*';
	}
}