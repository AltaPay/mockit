<?php

class MockitEventList
	implements IteratorAggregate
{
	private $events;

	public function __construct($events = array())
	{
		$this->events = $events;
	}

	/**
	 * @return MockitEvent[]
	 */
	public function getIterator() : Traversable
	{
		return new ArrayIterator($this->events);
	}

	public function add(MockitEvent $event)
	{
		$this->events[] = $event;
	}

	public function isEmpty()
	{
		return $this->count() == 0;
	}

	public function first()
	{
		return $this->events[0];
	}

	public function count()
	{
		return count($this->events);
	}

	public function shift()
	{
		return array_shift($this->events);
	}

	public function copy()
	{
		return new MockitEventList($this->events);
	}

	/**
	 * @return MockitVerifierResult
	 */
	public function getVerifyResult(Mockit $mock, MockitEvent $matchEvent)
	{
		$result = new MockitVerifierResult();
		foreach($this->getIterator() as $event)
		{
			if($mock !== $event->getMock())
			{
				continue;
			}

			$matchResult = $matchEvent->matches($event);
			if($matchResult->matches())
			{
				$result->increaseFoundCount();
				$result->addMatchResult($matchResult);
			}
			if($matchResult->methodMatches())
			{
				$result->increaseMethodFoundCount();
			}
			if($matchResult->methodMatches() && !$matchResult->matches())
			{
				$result->addMethodMatchResult($matchResult);
			}
		}
		return $result;
	}

	public function verifySubsequentCalls(MockitEvent $event, MockitExpectedCount $expectedCount, MockitMatchResult $previousMatch = null)
	{
		$del=0;
		$found = false;


		foreach($this->getIterator() as $unmatchedEvent)
		{
			$del++;
			if(is_null($unmatchedEvent))
			{
				throw new Exception("No more events in the events queue, but we expected ".$event->eventDescription()." to be called");
			}

			$isInInOrderSequence = Mockit::getVerificationMatches()->matchesAny($unmatchedEvent);
			$matchResult = $event->matches($unmatchedEvent);
			if($isInInOrderSequence && !$matchResult->matches())
			{
				// Will fail on too many subsequent calls of already matched events, that are not verified in ordere
				throw new MockitOutOfOrderInvokedException($matchResult, $previousMatch, $event);
			}
			else if($matchResult->matches())
			{
				$event->getMock()->addVerificationMatch($matchResult);
				$found = true;
				break;
			}
		}
		if($found)
		{
			for($i=0;$i<$del;$i++)
			{
				$event->getMock()->shiftUnmatchedEvents();
			}
		}
		else if($expectedCount->getCount() > 0 || is_null($expectedCount->getCount()))
		{
			// Will fail when not enough subsequent invocations are done
			throw new MockitVerificationException($event->eventDescription().' was not called (at least not in the correct order)');
		}

	}
}