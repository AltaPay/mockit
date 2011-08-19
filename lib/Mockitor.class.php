<?php

class Mockitor
{
	private $mock;
	
	public function __construct(Mockit $mock)
	{
		$this->mock = $mock;
	}
	
	public function __call($name, $arguments)
	{
		return $this->mock->process(new MockitEvent($name, $arguments));
	}
}