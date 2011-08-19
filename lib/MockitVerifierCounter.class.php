<?php

class MockitVerifierCounter
{
	private $foundCount;
	
	/**
	 * @var MockitVerifier
	 */
	private $verifier;
	
	public function __construct(MockitVerifier $verifier, $foundCount)
	{
		$this->foundCount = $foundCount;
		$this->verifier = $verifier;
	}
	
	public function exactly($times)
	{
		if($this->foundCount != $times)
		{
			$this->throwException($times);
		}
	}
	
	public function once()
	{
		if($this->foundCount != 1)
		{
			$this->throwException(1);
		}
	}
	
	public function never()
	{
		if($this->foundCount != 0)
		{
			$this->throwException(0);
		}
	}
	
	private function throwException($times)
	{
		throw new Exception($this->verifier->_getEvent()->getName().' was expected to be called with the correct arguments '.$times.' times, but was called '.$this->foundCount.' times');
	}
}