<?php

class MockitDelegateEvent
	implements IMockitStubEvent
{
	private $delegate;
	
	public function __construct($delegate)
	{
		$this->delegate = $delegate;
	}
	
	public function execute($arguments)
	{
		return call_user_func_array($this->delegate, $arguments);
	}
}