<?php

require_once(dirname(__FILE__).'/MockitInclude.php');

class MockitTestCase extends PHPUnit_Framework_TestCase
{
	protected function tearDown()
	{
		parent::tearDown();
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

	public function xpath($xpath, $value)
	{
		return new MockitXpathMatcher($xpath, $value);
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

	public function xml($xmlString)
	{
		return new MockitXmlMatcher($xmlString);
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

	public function xmlDelegate($content)
	{
		return $this->delegate(function($var) use ($content) {
			return $var instanceof SimpleXMLExtended && (string)$var == $content;
		});
	}

	public function xmlDelegateXMLElement($content)
	{
		return $this->delegate(function($var) use ($content) {
			return $var instanceof SimpleXMLElement && (string)$var == $content;
		});
	}
}