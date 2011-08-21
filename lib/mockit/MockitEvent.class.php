<?php

class MockitEvent
{
	private $name;
	private $arguments;
	private $mock;
	
	public function __construct(Mockit $mock, $name, $arguments)
	{
		$this->name = $name;
		$this->arguments = $arguments;
		$this->mock = $mock;
	}
	
	public function getMock()
	{
		return $this->mock;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getArguments()
	{
		return $this->arguments;
	}
	
	/**
	 * @return MockitMatchResult
	 */
	public function matches(MockitEvent $event)
	{
		return new MockitMatchResult($this, $event);
	}
}