<?php

class MockitVerifier
	extends MockitMatcher
{
	public function __call($name, $arguments)
	{
		$this->event = new MockitEvent($name, $arguments);
		$foundCount = 0;
		foreach($this->mock->getEvents() as $event) /* @var $event MockitEvent */
		{
			if($this->event->matches($event)->matches())
			{
				$foundCount++;
			}
		}
		return new MockitVerifierCounter($this, $foundCount);
	}
}