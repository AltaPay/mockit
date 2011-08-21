<?php

class MockitOutOfOrderException
	extends MockitVerificationException
{
	public function __construct($actualOrder,$strict)
	{
		parent::__construct('Mocks was not called in the correct'.($strict?' strict':'').' order: '."\n".$this->prettyPrintOrder($actualOrder));
	}
	
	private function prettyPrintOrder($actualOrder)
	{
		$result = array();
		print count($actualOrder);
		foreach($actualOrder as $match) /* @var $match MockitInOrderMatch */
		{
			$result[] = $match->getDescription();
		}
		return implode("\n",$result);
	}
}