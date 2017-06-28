<?php

class MockitVerifierResult
{
	private $foundCount = 0;
	private $methodFoundCount = 0;
	/**
	 * @var MockitMatchResult[]
	 */
	private $methodMatchResults = array();
	/**
	 * @var MockitMatchResult[]
	 */
	private $matchResults = array();

	public function __construct()
	{

	}

	public function getFoundCount()
	{
		return $this->foundCount;
	}

	public function increaseFoundCount()
	{
		$this->foundCount++;
	}

	public function addMatchResult(MockitMatchResult $matchResult)
	{
		$this->matchResults[] = $matchResult;
	}

	public function increaseMethodFoundCount()
	{
		$this->methodFoundCount++;
	}

	public function getMethodFoundCount()
	{
		return $this->methodFoundCount;
	}

	public function addMethodMatchResult(MockitMatchResult $matchResult)
	{
		$this->methodMatchResults[$this->methodFoundCount] = $matchResult;
	}

	/**
	 * @return MockitMatchResult[]
	 */
	public function getMethodMatchResults()
	{
		return $this->methodMatchResults;
	}

	/**
	 * @return MockitMatchResult[]
	 */
	public function getMatchResults()
	{
		return $this->matchResults;
	}

	public function getMethodMatchDescription()
	{
		$result = array();
		foreach($this->methodMatchResults as $invocationCount => $methodMatch) /* @var $methodMatch MockitMatchResult */
		{
			$result[] = 'At invocation count '.$invocationCount.' argument matches were: '.$methodMatch->matchDescription();
		}
		return implode("\n",$result);
	}

}