<?php

class MockitInOrderMatch
{
	private $expectedIndex, $actualIndex;
	
	/**
	 * @var MockitEvent
	 */
	private $expectation;
	
	/**
	 * @var MockitMatchResult
	 */
	private $matchResult;
	
	public function __construct($expectedIndex, $actualIndex, MockitEvent $expectation, MockitMatchResult $matchResult)
	{
		$this->expectedIndex = $expectedIndex;
		$this->actualIndex = $actualIndex;
		$this->expectation = $expectation;
		$this->matchResult = $matchResult;
	}
	
	public function getDescription()
	{
		return Mockit::uniqueid($this->expectation->getMock()->instance())
			.'->'.$this->expectation->getName().''.$this->matchResult->argumentMatchDescription().' was expected to be called at index '
			.$this->expectedIndex.' but was called at index: '.$this->actualIndex;
	}
}