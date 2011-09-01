<?php

require_once(dirname(__FILE__).'/IMockitStubEvent.class.php');
require_once(dirname(__FILE__).'/matchers/IMockitMatcher.class.php');
require_once(dirname(__FILE__).'/MockitVerificationException.class.php');
require_once(dirname(__FILE__).'/MockitMatcher.class.php');
require_once(dirname(__FILE__).'/MockitMatchResult.class.php');
require_once(dirname(__FILE__).'/MockitStub.class.php');
require_once(dirname(__FILE__).'/IMockit.class.php');
require_once(dirname(__FILE__).'/Mockit.class.php');
require_once(dirname(__FILE__).'/MockitOutOfOrderException.class.php');
require_once(dirname(__FILE__).'/MockitEvent.class.php');
require_once(dirname(__FILE__).'/MockitParameterMatchResult.class.php');
require_once(dirname(__FILE__).'/MockitStubReturnEvent.class.php');
require_once(dirname(__FILE__).'/MockitStubThrowEvent.class.php');
require_once(dirname(__FILE__).'/MockitVerifier.class.php');
require_once(dirname(__FILE__).'/MockitTestCase.class.php');
require_once(dirname(__FILE__).'/MockitRecursiveMatcher.class.php');
require_once(dirname(__FILE__).'/matchers/MockitRegexMatcher.class.php');
require_once(dirname(__FILE__).'/matchers/MockitEqualsMatcher.class.php');
require_once(dirname(__FILE__).'/matchers/MockitAnyMatcher.class.php');
require_once(dirname(__FILE__).'/matchers/MockitSameMatcher.class.php');
require_once(dirname(__FILE__).'/matchers/CaptorMatcher.class.php');
require_once(dirname(__FILE__).'/matchers/NotMatcher.class.php');
require_once(dirname(__FILE__).'/MockitRecursiveEvent.class.php');

class MockitTestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * @return IMockit
	 */
	public function getMockit($classname, $uniqueId=null)
	{
		Mockit::resetMocks();
		return new Mockit($classname, $uniqueId);
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
	
	/**
	 * @return CaptorMatcher
	 */
	public function captor()
	{
		return new CaptorMatcher();
	}
}