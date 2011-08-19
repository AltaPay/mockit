<?php

class Mockit
{
	/**
	* @var ReflectionClass
	*/
	private $class;
	
	/**
	 * @var Mockitor
	 */
	private $mockitor;
	
	private $events = array();
	private $matchers = array();
	
	public function __construct($classname)
	{
		$this->class = new ReflectionClass($classname);
		$this->mockitor = new Mockitor($this);
	}
	
	public function when()
	{
		$matcher = new MockitMatcher($this, $this->class);
		$this->matchers[] = $matcher;
		return $matcher;
	}
	
	public function verify()
	{
		return new MockitVerifier($this, $this->class);
	}
	
	public function instance()
	{
		return $this->mockitor;
	}
	
	public function getEvents()
	{
		return $this->events;
	}
	
	public function process(MockitEvent $event)
	{
		$this->events[] = $event;
		foreach($this->matchers as $matcher) /* @var $matcher MockitMatcher */
		{
			if($matcher->_getEvent()->matches($event)->matches())
			{
				return $matcher->_getStub()->_executeStub();
			}
		}
	}
}