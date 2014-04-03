<?php

class MockitVerifier
{
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

	private $inOrderInvoke = false;

	public function __construct(Mockit $mock,$expectedCount,$inOrderInvoke=false)
	{
		$this->mock = $mock;
		$this->expectedCount = $expectedCount;
		$this->inOrderInvoke = $inOrderInvoke;
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
		$clazz = new ReflectionClass($this->mock->getClassname());
		if($clazz->hasMethod($name))
		{
			$method = $clazz->getMethod($name); /* @var $method ReflectionMethod */
			if(count($method->getParameters()) != 0)
			{
				$methodParameters = $method->getParameters();
				for($i=0;$i<count($method->getParameters());$i++)
				{
					if(!isset($arguments[$i]))
					{
						$methodParameter = $methodParameters[$i]; /* @var $methodParameter ReflectionParameter */


						if($methodParameter->isOptional())
						{
							$arguments[$i] = $methodParameter->getDefaultValue();
						}
					}
				}
			}
		}

		$this->event = new MockitEvent($this->mock,$name, $arguments, count($this->mock->getVerificationMatches()));



		$foundCount = 0;
		$methodFoundCount = 0;
		$methodMatchResults = array();
		$matchResults = array();
		foreach($this->mock->getEvents() as $event) /* @var $event MockitEvent */
		{
			if($this->mock !== $event->getMock())
			{
				continue;
			}

			$matchResult = $this->event->matches($event);
			if($matchResult->matches())
			{
				$foundCount++;
				$matchResults[] = $matchResult;
			}
			if($matchResult->methodMatches())
			{
				$methodFoundCount++;
			}
			if($matchResult->methodMatches() && !$matchResult->matches())
			{
				$methodMatchResults[$methodFoundCount] = $matchResult;
			}
		}

		if(!(is_null($this->expectedCount)/* && $methodFoundCount == $foundCount*/) && $foundCount != $this->expectedCount)
		{
			$this->throwException($foundCount,$methodFoundCount, $methodMatchResults);
		}
		else if($this->expectedCount === 0 && (!(is_null($this->expectedCount) && $methodFoundCount == $foundCount) && $foundCount > $this->expectedCount))
		{
			$this->throwException($foundCount,$methodFoundCount, $methodMatchResults);
		}

		if(!$this->mock->getOutOfOrder())
		{
//			print "Matching for event: ";
//			print $this->event->eventDescription()."\n";

			$lastMatch = $this->mock->getLastVerificationMatch(); /* @var $lastMatch MockitMatchResult */
			$actualMatchResult = $this->getRelevantMatchResult($matchResults);

//			print "last match description: ".(is_null($lastMatch) ? "NULL" : $lastMatch->matchDescription())."\n";
//			print "actual match description: ".(is_null($actualMatchResult) ? "NULL" : $actualMatchResult->matchDescription())."\n";
			if(!is_null($actualMatchResult))
			{
				if(!is_null($lastMatch))
				{
					if($lastMatch->getMatchedEvent()->getIndex() >= $actualMatchResult->getMatchedEvent()->getIndex())
					{
						throw new MockitOutOfOrderException($lastMatch, $actualMatchResult);
					}
				}

				$this->mock->addVerificationMatch($actualMatchResult);
			}

			//if($this->inOrderInvoke)
			{
//				print "\nChecking event:".$this->event->eventDescription()." (".count($this->mock->getUnmatchedEvents()).")\n";
				//while(!is_null($nextUnmatchedEvent = $this->mock->nextUnmatchedEvent()))
				$del=0;
				$found = false;
				foreach($this->mock->getUnmatchedEvents() as $nextUnmatchedEvent) /* @var $nextUnmatchedEvent MockitEvent */
				{
					$del++;
//					$nextUnmatchedEvent = $this->mock->nextUnmatchedEvent();
					if(is_null($nextUnmatchedEvent))
					{
						throw new Exception("No more events in the events queue, but we expected ".$this->event->eventDescription()." to be called");
					}

					$matchesOne = false;
					$thisMatchesOne= false;
					foreach(Mockit::getVerificationMatches() as $verificationMatch) /* @var $verificationMatch MockitMatchResult */
					{
						if($verificationMatch->getVerificationEvent()->matches($nextUnmatchedEvent)->matches())
						{
							$matchesOne = true;
						}
						/*
						if($verificationMatch->getVerificationEvent()->matches($this->event)->matches())
						{
							$thisMatchesOne = true;
						}
						*/
					}

					$matchResult = $this->event->matches($nextUnmatchedEvent);
//					print "Matches?: ".$nextUnmatchedEvent->eventDescription()." == ".$this->event->eventDescription()."\n";
					if($matchesOne && !$matchResult->matches())
					{
//						print $nextUnmatchedEvent->eventDescription()."\n";
//						print "Matches one, but ".$nextUnmatchedEvent->eventDescription()." != ".$this->event->eventDescription()."\n";
						throw new MockitOutOfOrderInvokedException($matchResult, $lastMatch, $this->event);
					}
					else if($matchResult->matches())
					{
//						print "Matches!: ".$nextUnmatchedEvent->eventDescription()." == ".$this->event->eventDescription()."\n";
//						$this->mock->shiftUnmatchedEvents();
						$this->mock->addVerificationMatch($matchResult);
						$found = true;
						break;
					}
//					else if(!$thisMatchesOne)
//					{
//						print "this does not match any: ".$this->event->eventDescription()."\n";
//						continue;
//					}
//					else if(!$matchesOne)
//					{
//						print "does not match any: ".$nextUnmatchedEvent->eventDescription()."\n";
//						continue;
////						$this->mock->shiftUnmatchedEvents();
////						continue;
//					}
//					else
//					{
//						print 'wtf?!'."\n";	
//					}
				}
				if($found)
				{
					for($i=0;$i<$del;$i++)
					{
						$this->mock->shiftUnmatchedEvents();
					}
				}
				else if($this->expectedCount > 0)
				{
					throw new MockitVerificationException('Could not find match for: '.$this->event->eventDescription());
				}



//				else
//				{
//					
//					print Mockit::uniqueid($nextUnmatchedEvent->getMock()->instance()).'->'.$nextUnmatchedEvent->getName().'('.$nextUnmatchedEvent->getArgumentsAsString().')'."\n";
//					print Mockit::uniqueid($this->event->getMock()->instance()).'->'.$this->event->getName().'('.$this->event->getArgumentsAsString().')'."\n";
//					foreach(Mockit::getVerificationMatches() as $verificationMatch) /* @var $verificationMatch MockitMatchResult */
//					{
//						print "verification match result: [".Mockit::uniqueid($verificationMatch->getVerificationEvent()->getMock()->instance())."]->".$verificationMatch->getMatchedEvent()->getName()."\n";
//					}
//				}
//				print "umatched events: \n";
//				foreach($this->mock->getUnmatchedEvents() as $unmatchedEvent) /* @var $unmatchedEvent MockitEvent */
//				{
//					print $unmatchedEvent->eventDescription()."\n";
//				}
//				print "\n";


//				$ex = new Exception("these events are like not like eachother... man: ");
//				print $ex->getTraceAsString();
			}

		}

	}

	/**
	 * @return MockitMatchResult
	 */
	private function  getRelevantMatchResult($matchResults)
	{
		$index = 0;
		foreach($this->mock->getVerificationMatches() as $verificationMatch) /* @var $verificationMatch MockitMatchResult */
		{
			foreach($matchResults as $matchResult) /* @var $matchResult MockitMatchResult */
			{
				if($verificationMatch->getMatchedEvent() === $matchResult->getMatchedEvent())
				{
					$index++;
					break;
				}
			}

		}

		return @$matchResults[$index];
	}

	private function throwException($foundCount, $methodFoundCount, $methodMatchResults)
	{
		if(is_null($this->expectedCount))
		{
			throw new MockitVerificationException('Method expected to be called any number of times, but with the correct arguments. Argument matches were: '."\n".$this->getArgumentDescriptions($methodMatchResults));
		}
		if($methodFoundCount == 0)
		{
			throw new MockitVerificationException('Method was expected to be called '.$this->expectedCount.' times, but was called '.$methodFoundCount.' times:'."\n".Mockit::uniqueid($this->mock->instance()).'->'.$this->event->getName().'('.$this->event->getArgumentsAsString().')');
		}
		else if($methodFoundCount == $this->expectedCount && $foundCount != $this->expectedCount)
		{
			throw new MockitVerificationException('Method was called the correct number of times, but with incorrect parameters: '."\n".$this->getArgumentDescriptions($methodMatchResults));
		}
		else if($foundCount == $methodFoundCount)
		{
			throw new MockitVerificationException('Method was expected to be called with the correct arguments '.$this->expectedCount.' times, but was called '.$foundCount.' times with the correct arguments');
		}
		else
		{
			throw new MockitVerificationException('Method was expected to be called with the correct arguments '.$this->expectedCount.' times, but was called '.$methodFoundCount.' times but only '.$foundCount.' with the correct arguments. Incorrect matches were: '."\n".$this->getArgumentDescriptions($methodMatchResults));
		}
	}

	private function getArgumentDescriptions($methodMatchResults)
	{
		$result = array();
		foreach($methodMatchResults as $invocationCount => $methodMatch) /* @var $methodMatch MockitMatchResult */
		{
			$result[] = 'At invocation count '.$invocationCount.' argument matches were: '.$methodMatch->matchDescription();
		}
		return implode("\n",$result);
	}
}
