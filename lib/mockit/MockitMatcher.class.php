<?php

class MockitMatcher
{
	/**
	 * @var Mockit
	 */
	protected $mock;
	
	protected $event;
	private $stub;
	
	/**
	 * @var ReflectionClass
	 */
	private $class;
	
	public function __construct(Mockit $mock, ReflectionClass $class)
	{
		$this->mock = $mock;
		$this->class = $class;
		$this->stub = new MockitStub();
	}
	
	/**
	 * @var MockitEvent
	 */
	public function _getEvent()
	{
		return $this->event;
	}
	
	/**
	 * 
	 * @var MockitStub
	 */
	public function _getStub()
	{
		return $this->stub;
	}
	
	public function __call($name, $arguments)
	{
		if(count($arguments) == 0)
		{
			$method = $this->class->getMethod($name); /* @var $method ReflectionMethod */
			if(count($method->getParameters()) != 0)
			{
				for($i=0;$i<count($method->getParameters());$i++)
				{
					$arguments[] = new MockitAnyMatcher();
				}
			}
		}
		
		$this->event = new MockitEvent($this->mock, $name, $arguments,-1);
		return $this->stub;
	}
}