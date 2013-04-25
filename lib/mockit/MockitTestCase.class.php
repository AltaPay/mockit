<?php

require_once(dirname(__FILE__).'/MockitInclude.php');

class MockitTestCase extends PHPUnit_Framework_TestCase
{
	protected function tearDown()
	{
		Mockit::resetMocks();
	}
	
	/**
	 * @return IMockit
	 */
	public function getMockit($classname, $uniqueId=null)
	{
		return new Mockit($classname, $uniqueId);
	}

	/**
	 * @return IMockit
	 */
	public function getSpy($object)
	{
		return new Mockit($object);
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
	
	public function equals($val)
	{
		return new MockitEqualsMatcher($val);
	}

	public function not(IMockitMatcher $childMatcher)
	{
		return new NotMatcher($childMatcher);
	}

	public function type($type)
	{
		return new MockitTypeMatcher($type);
	}

	/**
	 * @return CaptorMatcher
	 */
	public function captor()
	{
		return new CaptorMatcher();
	}
	
	public function delegate($delegate)
	{
		return new MockitDelegateMatcher($delegate);
	}
}