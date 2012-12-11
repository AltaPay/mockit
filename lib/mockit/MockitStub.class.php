<?php

class MockitStub
{
	private $actions = array();
	private $values = array();
	private $exceptions = array();
	
	/**
	 * @var Mockit
	 */
	private $mock;
	/**
	 * @var ReflectionClass
	 */
	private $class;
	private $methodName;
	
	public function __construct(Mockit $mock, ReflectionClass $class, $methodName)
	{
		$this->mock = $mock;
		$this->class = $class;
		$this->methodName = $methodName;
	}
	
	public function thenReturn($value)
	{
		$this->actions[] = new MockitStubReturnEvent($value);
		return $this;
	}
	
	public function thenReturnUnique()
	{
		$value = $this->getUniqueValue();
		$this->actions[] = new MockitStubReturnEvent($value);
		return $value;
	}
	
	public function thenThrow($exception)
	{
		$this->actions[] = new MockitStubThrowEvent($exception);
		return $this;
	}
	
	public function delegate($delegate)
	{
		$this->actions[] = new MockitDelegateEvent($delegate);
		return $this;
	}
	
	public function _executeStub($arguments)
	{
		$action = array_shift($this->actions); /* @var $action IMockitStubEvent */
		if(count($this->actions) == 0)
		{
			$this->actions[] = $action;
		}
		
		return $action->execute($arguments);
	}
	
	private function getUniqueValue()
	{
		$ex = new Exception();
		$trace = $ex->getTrace();
		
		$id = $this->methodName."[".basename(@$trace[1]['file']).':'.@$trace[1]['line']."]";
		
		$reflectionMethod = $this->class->getMethod($this->methodName);
		if(preg_match('/\@return\s+([^\s]+)/',$reflectionMethod->getDocComment(),$matches))
		{
			$returnClass = $matches[1];
			if(class_exists($returnClass))
			{
				$mock = new Mockit($returnClass, $id);
				return $mock->instance();
			}
		}
		
		return $id;
		
	}
}