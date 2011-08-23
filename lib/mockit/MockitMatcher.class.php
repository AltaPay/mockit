<?php

class MockitMatcher
{
	/**
	 * @var ReflectionClass
	 */
	private $class;
	
	/**
	 * @var Mockit
	 */
	protected $mock;
	
	protected $event;
	private $stub;
	
	public function __construct(Mockit $mock,ReflectionClass $class)
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
		$this->event = new MockitEvent($this->mock, $name, $arguments,-1);
		return $this->stub;
	}
}