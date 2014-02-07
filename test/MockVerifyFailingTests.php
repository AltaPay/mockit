<?php
require dirname(__FILE__).'/../autoload.php';

class MockVerifyFailingTests
	extends MockitTestCase
{
	/**
	 * @expectedException MockitVerificationException
	 */
	public function testFailingPassMultipleArguments()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$instance->multipleArguments('arg3','arg2');
	
		$mock->once()->multipleArguments('arg1','arg2');
	}
	
	/**
	 * @expectedException MockitVerificationException
	 */
	public function testFailingPassMultipleArgumentsWithAnyMatch()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$instance->multipleArguments('arg3','arg2');
	
		$mock->once()->multipleArguments('arg1',$this->any());
	}
	
	/**
	 * @expectedException MockitVerificationException
	 */
	public function testSameMatcherThatFails()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$dummy = new MyDummy();
		$otherDummy = new MyDummy();
	
		$instance->addDummy($otherDummy);
	
		$mock->once()->addDummy($this->same($dummy));
	}
	
	/**
	 * @expectedException MockitVerificationException
	 */
	public function testSameMatcherWithMockThatFails()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$instance2 = $this->getMockit('MyDummy')->instance();
	
		$instance->addDummy($this->getMockit('MyDummy')->instance());
	
		$mock->once()->addDummy($this->same($instance2));
	}
	
	/**
	 * @expectedException MockitVerificationException
	 */
	public function testObjectEqualsMatchingFailing()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$obj1 = new ValueObject();
		$obj1->setProperty('prop1');
	
		$obj2 = new ValueObject();
		$obj2->setProperty('prop2');
	
		$instance->addValueObject($obj1);
	
		$mock->once()->addValueObject($obj2);
	}
	
	/**
	 * @expectedException MockitVerificationException
	 */
	public function testObjectEqualsMatchingFailingForMethodWithMultipleParameters()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance(); /* @var $instance MyDummy */
	
		$obj1 = new ValueObject();
		$obj1->setProperty('prop1');
	
		$obj2 = new ValueObject();
		$obj2->setProperty('prop2');
	
		$instance->multipleArguments($obj1, $obj2);
	
		$mock->once()->multipleArguments($obj2, $obj2);
	}
	
	/**
	 * @expectedException MockitOutOfOrderException
	 */
	public function testInOrderVerificationsSimpleFailing()
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
	
	/**
	 * @expectedException MockitOutOfOrderException
	 */
	public function testInOrderVerificationsMultipleIrrelevantCalls()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		$mock2 = $this->getMockit('MyDummy');
		$instance2 = $mock2->instance();
		
		$instance2->doIt('noget tredje');
		$instance->doIt('1');
		$instance->doIt('noget andet');
		$instance2->doIt('2');
		$instance->doIt('helt i hegnet');
	
		$mock2->once()->doIt('2');
		$mock->once()->doIt('1');
		
		$mock->once()->doIt('1');
		$mock->once()->doIt('2');
	}

	/**
	 * @expectedException MockitVerificationException
	 */
	public function testRecursiveMockWithFailingVerification()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->with()->addDummy($this->any())->when()->doIt($this->any())->thenReturn('wah');
		
		$this->assertEquals('wah',$instance->addDummy($instance)->doIt('1'));
		
		$mock->with()->addDummy($this->any())->once()->doIt('2');
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testRecursiveMockOfMethodThatReturnsArrayThrowsExplainingException()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();

		$mock->with()->getDummies()->doSomething();
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testRecursiveMockOfMethodThatReturnsUnmockableObjectThrowsExplainingException()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();

		$mock->with()->doIt('1')->doSomething();
	}

	/**
	 * @expectedException MockitVerificationException
	 */
	public function test_noFurtherInvocations_throwExceptionIfThereIsAnInvocation()
	{
		$mock = $this->getMockit('MyDummy')->recursive();

		$mock->instance()->getDummy();

		$mock->noFurtherInvocations();
	}

	/**
	 * @expectedException MockitVerificationException
	 */
	public function test_noFurtherInvocations_throwExceptionIfThereIsFurtherInvocations()
	{
		$mock = $this->getMockit('MyDummy')->recursive();

		$mock->instance()->getDummy();
		$mock->instance()->getDummy();

		$mock->once()->getDummy();
		$mock->noFurtherInvocations();
	}
}



