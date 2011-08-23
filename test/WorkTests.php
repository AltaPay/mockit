<?php
require dirname(__FILE__).'/../autoload.php';

class MockFailingTests
	extends MockitTestCase
{
	public function testwork()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		$mock2 = $this->getMockit('MyDummy');
		$instance2 = $mock2->instance();
		
		$instance->doIt('1');
		$instance2->doIt('2');
		
		$mock2->once()->doIt('2');
		$mock->once()->doIt('1');
	}
}