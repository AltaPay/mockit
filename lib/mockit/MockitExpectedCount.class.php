<?php

class MockitExpectedCount
{
	private $count;
	private $isInvoked;

	private function __construct($count, $isInvoked)
	{
		$this->count = $count;
		$this->isInvoked = $isInvoked;
	}

	public function isInvoked()
	{
		return $this->isInvoked;
	}

	public function getCount()
	{
		return $this->count;
	}

	public static function invoked()
	{
		return new MockitExpectedCount(null, true);
	}

	public static function get($count)
	{
		return new MockitExpectedCount($count, false);
	}

	public function throwExceptionsOnMismatch(MockitVerifierResult $verifyResult, MockitEvent $verificationEvent)
	{
		if(!(is_null($this->count)/* && $methodFoundCount == $foundCount*/) && $verifyResult->getFoundCount() != $this->count)
		{
			$this->throwException($verifyResult,$verificationEvent);
		}
		else if($this->count === 0 && (!(is_null($this->count) && $verifyResult->getMethodFoundCount() == $verifyResult->getFoundCount()) && $verifyResult->getFoundCount() > $this->count))
		{
			$this->throwException($verifyResult,$verificationEvent);
		}
	}

	private function throwException(MockitVerifierResult $result, MockitEvent $verificationEvent)
	{
		if(is_null($this->count))
		{
			throw new MockitVerificationException('Method expected to be called any number of times, but with the correct arguments. Argument matches were: '."\n".$result->getMethodMatchDescription());
		}
		if($result->getMethodFoundCount() == 0)
		{
			throw new MockitVerificationException('Method was expected to be called '.$this->count.' times, but was called '.$result->getMethodFoundCount().' times:'."\n".Mockit::uniqueid($verificationEvent->getMock()->instance()).'->'.$verificationEvent->getName().'('.$verificationEvent->getArgumentsAsString().')');
		}
		else if($result->getMethodFoundCount() == $this->count && $result->getFoundCount() != $this->count)
		{
			throw new MockitVerificationException('Method was called the correct number of times, but with incorrect parameters: '."\n".$result->getMethodMatchDescription());
		}
		else if($result->getFoundCount() == $result->getMethodFoundCount())
		{
			throw new MockitVerificationException('Method was expected to be called with the correct arguments '.$this->count.' times, but was called '.$result->getFoundCount().' times with the correct arguments');
		}
		else
		{
			throw new MockitVerificationException('Method was expected to be called with the correct arguments '.$this->count.' times, but was called '.$result->getMethodFoundCount().' times but only '.$result->getFoundCount().' with the correct arguments. Incorrect matches were: '."\n".$result->getMethodMatchDescription());
		}
	}
}