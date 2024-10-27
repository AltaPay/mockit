<?php

require_once dirname(__FILE__).'/../autoload.php';

class MockInOrderTest
    extends MockitTestCase
{
    /**
     * @Test
     */
	public function testInOrderSuccessCase()
	{
		$mock = Mockit::getMock('MyDummy','d1');
		$instance = $mock->instance();
		
		$mock2 = Mockit::getMock('MyDummy','d2');
		$instance2 = $mock2->instance();
		
		$instance->getDummy();
		$instance2->getDummy();
		$instance->getDummy();
		
		$mock->invoked()->getDummy();
		$mock2->invoked()->getDummy();
		$mock->invoked()->getDummy();
	}
	
	/**
     * @Test
	 * @expectedException MockitOutOfOrderInvokedException
	 */
	public function testInOrder_failOnTooManyCallsBetweenOutliers()
	{
		$mock = Mockit::getMock('MyDummy', 'd1');
		$instance = $mock->instance();
		
		$mock2 = Mockit::getMock('MyDummy','d2');
		$instance2 = $mock2->instance();
		
		$instance->getDummy();
		$instance2->getDummy();
		$instance2->getDummy();
		$instance->getDummy();
		
		$mock->invoked()->getDummy();
		$mock2->invoked()->getDummy();
		$mock->invoked()->getDummy();
	}

	/**
     * @Test
	 * @expectedException MockitOutOfOrderInvokedException
	 */
	public function testInOrder_failOnTooManyCallsBeforeOutlier()
	{
		$mock = Mockit::getMock('MyDummy', 'd1');
		$instance = $mock->instance();

		$mock2 = Mockit::getMock('MyDummy','d2');
		$instance2 = $mock2->instance();

		$instance2->getDummy();
		$instance2->getDummy();
		$instance->getDummy();

		$mock2->invoked()->getDummy();
		$mock->invoked()->getDummy();
	}

	/**
     * @Test
	 * Handling this case would require checking it in the teardown method, which would overcomplicate matters
	 */
	public function testInOrder_failOnTooManyCallsAfterOutlier_willNotFail()
	{
		$mock = Mockit::getMock('MyDummy', 'd1');
		$instance = $mock->instance();

		$mock2 = Mockit::getMock('MyDummy','d2');
		$instance2 = $mock2->instance();

		$instance->getDummy();
		$instance2->getDummy();
		$instance2->getDummy();


		$mock->invoked()->getDummy();
		$mock2->invoked()->getDummy();
	}

    /**
     * @Test
     */
	public function testInOrderWithNonCheckedMockCalledInBetween()
	{
		$mock  = Mockit::getMock('MyDummy','d1');
		$instance = $mock->instance();
		
		$mock2 = Mockit::getMock('MyDummy','d2');
		$instance2 = $mock2->instance();
		
		$mock3 = Mockit::getMock('MyDummy','d3');
		$instance3 = $mock3->instance();
		
		$instance->getDummy();
		$instance2->getDummy();
		$instance3->getDummy();
		$instance->getDummy();
		
		$mock->invoked()->getDummy();
		$mock2->invoked()->getDummy();
		$mock->invoked()->getDummy();
	}

    /**
     * @Test
     */
	public function testInOrderWithNonCheckedMockCalledAround()
	{
		$mock  = Mockit::getMock('MyDummy','d1');
		$instance = $mock->instance();
		
		$mock2 = Mockit::getMock('MyDummy','d2');
		$instance2 = $mock2->instance();
		
		$mock3 = Mockit::getMock('MyDummy','d3');
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
     * @Test
	 * @expectedException MockitVerificationException
	 */
	public function testWorking()
	{
		$mock  = Mockit::getMock('MyDummy','d1');
		$instance = $mock->instance();
		
		$mock2 = Mockit::getMock('MyDummy','d2');
		$instance2 = $mock2->instance();
		
		$instance->getDummy();
		$instance->getDummy();
		$instance2->getDummy();
		
		$mock->invoked()->getDummy();
		$mock->invoked()->getDummy();
		$mock->invoked()->getDummy();
		$mock2->invoked()->getDummy();
	}

    /**
     * @Test
     */
	public function test_inOrderWithMultipleIrrelevantAroundIt()
	{
		$mock = Mockit::getMock('MyDummy');
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



