<?php
require dirname(__FILE__).'/../autoload.php';

class WorkTests
	extends MockitTestCase
{

	public function test_nothingReally_thisMethodsJustNeedsToBeHere()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();

		$instance->doIt('hat1');
		$instance->doIt('hat2');

		$mock->invoked()->doIt('hat1');
		$mock->invoked()->doIt('hat2');
	}

}