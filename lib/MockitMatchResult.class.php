<?php

class MockitMatchResult
{
	private $matches = true;
	private $methodMatches = true;
	private $expectedName;
	private $actualName;
	private $parameterMatches = array();
	
	public function __construct(MockitEvent $expected, MockitEvent $actual)
	{
		$this->matchName($expected->getName(), $actual->getName());
		if(is_array($expected->getArguments()))
		{
			$actualArguments = $actual->getArguments();
			foreach($expected->getArguments() as $i => $argument)
			{
				$this->parameterMatch($argument, $actualArguments[$i]);
			}
		}
	}
	
	private function matchName($expected, $actual)
	{
		$this->methodMatches = $this->matches = $expected == $actual;
		$this->expectedName = $expected;
		$this->actualName = $actual;
	}
	
	private function parameterMatch($expected, $actual)
	{
		$parameterMatch = new MockitParameterMatchResult($expected, $actual);
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
	
	public function argumentMatchDescription()
	{
		$result = array();
		foreach($this->parameterMatches as $argumentMatch) /* @var $argumentMatch MockitParameterMatchResult */
		{
			$result[] = $argumentMatch->matchDescription();
		}
		return '('.implode("\n, ",$result).')';
	}
}