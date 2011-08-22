<?php

class MockitTestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * @return Mockit
	 */
	public function getMock($classname)
	{
		return new Mockit($classname);
	}
	
	public function getInOrder($strict=false)
	{
		return new MockitInOrder($strict);
	}
	
	public function any()
	{
		return new MockitAnyMatcher();
	}
	
	public function same($obj)
	{
		return new MockitSameMatcher($obj);
	}
	
	public function regex($regex)
	{
		return new MockitRegexMatcher($regex);
	}
}