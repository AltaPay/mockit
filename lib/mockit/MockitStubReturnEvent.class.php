<?php

class MockitStubReturnEvent
	implements IMockitStubEvent
{
	private $value;
	
	public function __construct($value)
	{
		$this->value = $value;
	}
	
	public function execute()
	{
		return $this->value;
	}
}