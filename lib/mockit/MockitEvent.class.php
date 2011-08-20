<?php

class MockitEvent
{
	private $name;
	private $arguments;
	
	public function __construct($name, $arguments)
	{
		$this->name = $name;
		$this->arguments = $arguments;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getArguments()
	{
		return $this->arguments;
	}
	
	public function matches(MockitEvent $event)
	{
		return new MockitMatchResult($this, $event);
		if($this->getName() != $event->getName())
		{
			return false;
		}
		
		if(is_array($this->getArguments()))
		{
			$matchingArguments = array();
			foreach($this->getArguments() as $i => $argument)
			{
				if(!($argument instanceof IMockitMatcher))
				{
					$matchingArguments[] = new MockitEqualsMatcher($argument);
				}
				else
				{
					$matchingArguments[] = $argument;
				}
			}
			$otherArguments = $event->getArguments();
			foreach($matchingArguments as $i => $argument) /* @var $argument IMockitMatcher */
			{
				if(!$argument->matches($otherArguments[$i]))
				{
					return false;
				}
			}
		}
		
		return true;
	}
}