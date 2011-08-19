<?php

class MockitStub
{
	private $value;
	private $exception;
	
	public function thenReturn($value)
	{
		$this->value = $value;
	}
	
	public function thenThrow($exception)
	{
		$this->exception = $exception;
	}
	
	public function _executeStub()
	{
		if(!is_null($this->exception))
		{
			throw $this->exception;
		}
		else
		{
			return $this->value;
		}
	}
}