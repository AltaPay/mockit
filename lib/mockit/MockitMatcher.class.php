<?php

class MockitMatcher
{
	/**
	 * @var Mockit
	 */
	protected $mock;
	
	protected $event;
	private $stubs = array();
	
	/**
	 * @var ReflectionClass
	 */
	private $class;
	
	public function __construct(Mockit $mock, ReflectionClass $class)
	{
		$this->mock = $mock;
		$this->class = $class;
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
	public function _getStub($name)
	{
		if(!isset($this->stubs[$name]))
		{
			$this->stubs[$name] = new MockitStub($this->mock, $this->class , $name);
		}
		return $this->stubs[$name];
	}
	
	
	public function __call($name, array $arguments) //todo doublecheck
	{
		if(count($arguments) == 0 && $this->class->hasMethod($name))
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
		return $this->_getStub($name);
	}
}