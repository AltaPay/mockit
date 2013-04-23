<?php

class MockitOutOfOrderInvokedException
	extends MockitVerificationException
{
	public function __construct(MockitMatchResult $previousMatch, MockitMatchResult $thisMatch, MockitEvent $expectedEvent)
	{
		parent::__construct($thisMatch->matchDescription().' was not expected to be called after '.$previousMatch->matchDescription().', we expected: '.$expectedEvent->eventDescription());
	}
}