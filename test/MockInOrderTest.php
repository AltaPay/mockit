<?php

require dirname(__FILE__).'/../autoload.php';

class MockInOrderTest
	extends MockitTestCase
{
	public function testInOrderSuccessCase()
	{
		$mock = $this->getMockit('MyDummy','d1');
		$instance = $mock->instance();
		
		$mock2 = $this->getMockit('MyDummy','d2');
		$instance2 = $mock2->instance();
		
		$instance->getDummy();
		$instance2->getDummy();
		$instance->getDummy();
		
		$mock->invoked()->getDummy();
		$mock2->invoked()->getDummy();
		$mock->invoked()->getDummy();
	}
	
	/**
	 * @expectedException MockitOutOfOrderInvokedException
	 */
	public function testInOrderFailCase()
	{
		$mock = $this->getMockit('MyDummy', 'd1');
		$instance = $mock->instance();
		
		$mock2 = $this->getMockit('MyDummy','d2');
		$instance2 = $mock2->instance();
		
		$instance->getDummy();
		$instance2->getDummy();
		$instance2->getDummy();
		$instance->getDummy();
		
		$mock->invoked()->getDummy();
		$mock2->invoked()->getDummy();
		$mock->invoked()->getDummy();
	}
	
	public function testInOrderWithNonCheckedMockCalledInBetween()
	{
		$mock  = $this->getMockit('MyDummy','d1');
		$instance = $mock->instance();
		
		$mock2 = $this->getMockit('MyDummy','d2');
		$instance2 = $mock2->instance();
		
		$mock3 = $this->getMockit('MyDummy','d3');
		$instance3 = $mock3->instance();
		
		$instance->getDummy();
		$instance2->getDummy();
		$instance3->getDummy();
		$instance->getDummy();
		
		$mock->invoked()->getDummy();
		$mock2->invoked()->getDummy();
		$mock->invoked()->getDummy();
	}
	
	
	public function testInOrderWithNonCheckedMockCalledAround()
	{
		$mock  = $this->getMockit('MyDummy','d1');
		$instance = $mock->instance();
		
		$mock2 = $this->getMockit('MyDummy','d2');
		$instance2 = $mock2->instance();
		
		$mock3 = $this->getMockit('MyDummy','d3');
		$instance3 = $mock3->instance();
		
		$instance3->getDummy();
		$instance->getDummy();
		$instance2->getDummy();
		$instance->getDummy();
		$instance3->getDummy();
		
		$mock->invoked()->getDummy();
		$mock2->invoked()->getDummy();
		$mock->invoked()->getDummy();
	}
	
	
	/**
	 * @expectedException MockitVerificationException
	 */
	public function testWorking()
	{
		$mock  = $this->getMockit('MyDummy','d1');
		$instance = $mock->instance();
		
		$mock2 = $this->getMockit('MyDummy','d2');
		$instance2 = $mock2->instance();
		
		$instance->getDummy();
		$instance->getDummy();
		$instance2->getDummy();
		
		$mock->invoked()->getDummy();
		$mock->invoked()->getDummy();
		$mock->invoked()->getDummy();
		$mock2->invoked()->getDummy();
	}

	public function test_inOrderWithMultipleIrrelevantAroundIt()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();


		$instance->getDummies();
		$instance->doIt('asdf');
		$instance->getDummy();
		$instance->getDummies();
		$instance->doIt('asdf');

		$mock->invoked()->getDummy();
		$mock->invoked()->getDummies();
		$mock->invoked()->doIt('asdf');

	}
}



