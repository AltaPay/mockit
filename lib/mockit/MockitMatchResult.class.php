<?php

class MockitMatchResult
{
	private $matches = true;
	private $methodMatches = true;
	private $expectedName;
	private $actualName;
	private $parameterMatches = array();
	private $verificationEvent;
	private $matchedEvent;
	
	public function __construct(MockitEvent $expected, MockitEvent $actual)
	{
		$this->verificationEvent = $expected;
		$this->matchedEvent = $actual;
		$this->matchName($expected->getName(), $actual->getName());
		if($expected->getMock() !== $actual->getMock())
		{
			$this->matches = false;
		}
		if($this->methodMatches && $this->matches && is_array($expected->getArguments()))
		{
			if($expected->getMock()->isDynamic())
			{
				$actualArguments = $actual->getArguments();
				foreach($expected->getArguments() as $i => $argument)
				{
					$this->parameterMatch(null,$argument, @$actualArguments[$i]);
				}
			}
			else
			{
				$method = $expected->getMock()->getReflectionClass()->getMethod($expected->getName());
				$methodParameters = $method->getParameters();
				$actualArguments = $actual->getArguments();
				foreach($expected->getArguments() as $i => $argument)
				{
					if(!isset($methodParameters[$i]))
					{
						throw new Exception("A parameter was unexpectedly expected when calling: ".$expected->eventDescription());
					}
					$this->parameterMatch($methodParameters[$i],$argument, @$actualArguments[$i]);
				}
			}
		}
	}
	
	/**
	 * @return MockitEvent
	 */
	public function getVerificationEvent()
	{
		return $this->verificationEvent;
	}
	
	/**
	 * @return MockitEvent
	 */
	public function getMatchedEvent()
	{
		return $this->matchedEvent;
	}
	
	private function matchName($expected, $actual)
	{
		$this->methodMatches = $this->matches = $expected == $actual;
		$this->expectedName = $expected;
		$this->actualName = $actual;
	}
	
	private function parameterMatch(ReflectionParameter $reflectionParameter=null, $expected, $actual)
	{
		$parameterMatch = new MockitParameterMatchResult($reflectionParameter, $expected, $actual);
		if(!$parameterMatch->matches())
		{
			$this->matches = false;
		}
		$this->parameterMatches[] = $parameterMatch;
	}
	
	public function matches()
	{
		return $this->matches;
	}
	
	public function methodMatches()
	{
		return $this->methodMatches;
	}

	public function matchDescription()
	{
		return Mockit::uniqueid($this->getMatchedEvent()->getMock()->instance()).'->'.$this->getMatchedEvent()->getName().''.$this->argumentMatchDescription();
	}
	
	public function argumentMatchDescription()
	{
		$result = array();
		foreach($this->parameterMatches as $argumentMatch) /* @var $argumentMatch MockitParameterMatchResult */
		{
			$result[] = $argumentMatch->matchDescription();
		}
		return '('.implode(", ",$result).')';
	}
}