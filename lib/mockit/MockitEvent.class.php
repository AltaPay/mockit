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
	
	public function eventDescription()
	{
		return Mockit::uniqueid($this->getMock()->instance()).'->'.$this->getName().'('.$this->getArgumentsAsString().')';
	}
	
	public function getArgumentsAsString()
	{
		$ar = array();
		foreach($this->arguments as $argument)
		{
			$ar[] = Mockit::describeArgument($argument);
		}
		return implode(',',$ar);
	}
	
	/**
	 * @return MockitMatchResult
	 */
	public function matches(MockitEvent $event)
	{
		return new MockitMatchResult($this, $event);
	}
}