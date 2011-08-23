<?php

class MockitEvent
{
	private $name;
	private $arguments;
	private $mock;
	private $index;
	
	public function __construct(Mockit $mock, $name, $arguments, $index)
	{
		$this->name = $name;
		$this->arguments = $arguments;
		$this->mock = $mock;
		$this->index = $index;
	}
	
	public function getIndex()
	{
		return $this->index;
	}

	/**
	 * @return Mockit
	 */
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