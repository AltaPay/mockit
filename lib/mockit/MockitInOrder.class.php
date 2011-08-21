<?php

class MockitInOrder
{
	private $events = array();
	private $verificationEvents = array();
	private $strict;
	
	public function __construct($strict=false)
	{
		$this->strict = $strict;
	}
	
	public function addEvent(MockitEvent $event)
	{
		$this->events[] = $event;
	}
	
	public function getEvents()
	{
		return $this->events;
	}
	
	public function addVerification(MockitEvent $verificationEvent)
	{
		$this->verificationEvents[] = $verificationEvent;
	}
	
	public function verify()
	{
		$actualOrder = array();
		$actualMatches = array();
		foreach($this->events as $index => $event) /* @var $event MockitEvent */
		{
			foreach($this->verificationEvents as $expectedIndex => $verificationEvent) /* @var $verificationEvent MockitEvent */
			{
				if($verificationEvent->getMock() !== $event->getMock())
				{
					continue;
				}
				$matchResult = $verificationEvent->matches($event);
				if($matchResult->matches())
				{
					if($this->strict)
					{
						$actualMatches[] = new MockitInOrderMatch(count($actualOrder),$index,  $verificationEvent, $matchResult);
						$actualOrder[] = $index;
					}
					else
					{
						$actualMatches[] = new MockitInOrderMatch($expectedIndex,count($actualOrder), $verificationEvent, $matchResult);
						$actualOrder[] = $expectedIndex;
					}
					
					continue 2;
				}
			}
		}
		foreach($actualOrder as $key => $value)
		{
			if($key != $value)
			{
				throw new MockitOutOfOrderException($actualMatches,$this->strict);
			}
		}
	}
}