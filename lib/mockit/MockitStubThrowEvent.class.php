<?php

class MockitStubThrowEvent
	implements IMockitStubEvent
{
	private $exception;
	
	public function __construct(Exception $exception)
	{
		$this->exception = $exception;
	}
	
	public function execute($arguments)
	{
		throw $this->exception;
	}
}