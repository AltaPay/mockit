<?php

class MockitRecursiveEvent
{
	/**
	 * @var MockitEvent
	 */
	private $event;
	/**
	 * @var Mockit
	 */
	private $mock;
	
	public function __construct(MockitEvent $event, Mockit $mock)
	{
		$this->event = $event;
		$this->mock = $mock;
	}
	
	/**
	 * @return Mockit
	 */
	public function getMock()
	{
		return $this->mock;
	}
	
	/**
	 * @return MockitEvent
	 */
	public function getEvent()
	{
		return $this->event;
	}
}