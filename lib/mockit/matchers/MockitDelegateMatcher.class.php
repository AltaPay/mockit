<?php

class MockitDelegateMatcher
	implements IMockitMatcher
{
	private $delegate;
	
	public function __construct($delegate)
	{
		$this->delegate = $delegate;
	}
	
	public function matches($other)
	{
		return $this->m($other) === true;
	}
	
	private function m($other)
	{
		return call_user_func($this->delegate, $other);
	}

	function matchDescription($other)
	{
		if($this->matches($other))
		{
			return 'Other matches delegate';
		}
		else
		{
			return 'Other does not match delegate: '.$this->m($other);
		}
	}
	
	public function description()
	{
		return '*delegate*';
	}
}