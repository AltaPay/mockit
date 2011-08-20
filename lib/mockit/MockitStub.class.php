<?php

class MockitStub
{
	private $actions = array();
	private $values = array();
	private $exceptions = array();
	
	public function thenReturn($value)
	{
		$this->actions[] = new MockitStubReturnEvent($value);
		return $this;
	}
	
	public function thenThrow($exception)
	{
		$this->actions[] = new MockitStubThrowEvent($exception);
		return $this;
	}
	
	public function _executeStub()
	{
		$action = array_shift($this->actions); /* @var $action IMockitStubEvent */
		if(count($this->actions) == 0)
		{
			$this->actions[] = $action;
		}
		
		return $action->execute();
	}
}