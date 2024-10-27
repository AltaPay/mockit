<?php

class MockitOutOfOrderException
	extends MockitVerificationException
{
	public function __construct(MockitMatchResult $previousMatch, MockitMatchResult $thisMatch)
	{
		parent::__construct($thisMatch->matchDescription().' was not expected to be called after '.$previousMatch->matchDescription());
	}
}