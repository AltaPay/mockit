<?php

class MockitVerifier
{
	/**
	 * @var MockitExpectedCount
	 */
	private $expectedCount;

	/**
	 * @var Mockit
	 */
	private $mock;

	/**
	 *
	 * @var MockitEvent
	 */
	private $event;

	public function __construct(Mockit $mock,MockitExpectedCount $expectedCount)
	{
		$this->mock = $mock;
		$this->expectedCount = $expectedCount;
	}

	/**
	 * @return MockitEvent
	 */
	public function _getEvent()
	{
		return $this->event;
	}

	public function __call($name, $arguments)
	{
		$this->initializeEvent($name, $arguments);

		$verifyResult = $this->mock->getEvents()->getVerifyResult($this->mock, $this->event);

		$this->expectedCount->throwExceptionsOnMismatch($verifyResult,$this->event);

		if(!$this->mock->getOutOfOrder())
		{
			$previousMatch = $this->mock->getPreviousVerificationMatch(); /* @var $previousMatch MockitMatchResult */
			$actualMatchResult = $this->mock->getVerificationMatches()->getRelevantMatchResult($verifyResult->getMatchResults(),$previousMatch);

			if(!is_null($actualMatchResult))
			{
				// Is called after previous in order verification match
				$actualMatchResult->matchesInOrder($previousMatch);
			}

			// Is having more or less than expected subsequent calls for the same verification match
			$this->mock->getUnmatchedEvents()->verifySubsequentCalls($this->event, $this->expectedCount,$previousMatch);
		}
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return int
	 */
	private function initializeEvent($name, $arguments)
	{
		$clazz = new ReflectionClass($this->mock->getClassname());
		if ($clazz->hasMethod($name))
		{
			$method = $clazz->getMethod($name);
			/* @var $method ReflectionMethod */
			if (count($method->getParameters()) != 0)
			{
				$methodParameters = $method->getParameters();
				for ($i = 0; $i < count($method->getParameters()); $i++)
				{
					if (!isset($arguments[$i]))
					{
						$methodParameter = $methodParameters[$i];
						/* @var $methodParameter ReflectionParameter */


						if ($methodParameter->isOptional())
						{
							$arguments[$i] = $methodParameter->getDefaultValue();
						}
					}
				}
			}
		}

		$this->event = new MockitEvent($this->mock, $name, $arguments, $this->mock->getVerificationMatches()->count());
	}
}
