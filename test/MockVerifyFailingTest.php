<?php

require_once dirname(__FILE__).'/../autoload.php';

class MockVerifyFailingTest
    extends MockitTestCase
{
	/**
	 * @expectedException MockitVerificationException
     * @Test
     */
	public function testFailingPassMultipleArguments()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance->multipleArguments('arg3','arg2');
	
		$mock->once()->multipleArguments('arg1','arg2');
	}
	
	/**
	 * @expectedException MockitVerificationException
     * @Test
     */
	public function testFailingPassMultipleArgumentsWithAnyMatch()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance->multipleArguments('arg3','arg2');
	
		$mock->once()->multipleArguments('arg1',$this->any());
	}
	
	/**
	 * @expectedException MockitVerificationException
     * @Test
     */
	public function testSameMatcherThatFails()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
		$dummy = new MyDummy();
		$otherDummy = new MyDummy();
	
		$instance->addDummy($otherDummy);
	
		$mock->once()->addDummy($this->same($dummy));
	}
	
	/**
	 * @expectedException MockitVerificationException
     * @Test
     */
	public function testSameMatcherWithMockThatFails()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance2 = Mockit::getMock('MyDummy')->instance();
	
		$instance->addDummy(Mockit::getMock('MyDummy')->instance());
	
		$mock->once()->addDummy($this->same($instance2));
	}
	
	/**
	 * @expectedException MockitVerificationException
     * @Test
     */
	public function testObjectEqualsMatchingFailing()
	{
		$mock = Mockit::getMock('MyDummy');
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
     * @Test
     */
	public function testObjectEqualsMatchingFailingForMethodWithMultipleParameters()
	{
		$mock = Mockit::getMock('MyDummy');
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
     * @Test
     */
	public function testInOrderVerificationsSimpleFailing()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		$mock2 = Mockit::getMock('MyDummy');
		$instance2 = $mock2->instance();
		
		$instance->doIt('1');
		$instance2->doIt('2');
		
		$mock2->once()->doIt('2');
		$mock->once()->doIt('1');
	}
	
	/**
	 * @expectedException MockitOutOfOrderException
     * @Test
     */
	public function testInOrderVerificationsMultipleIrrelevantCalls()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		$mock2 = Mockit::getMock('MyDummy');
		$instance2 = $mock2->instance();
		
		$instance2->doIt('noget tredje');
		$instance->doIt('1');
		$instance->doIt('noget andet');
		$instance2->doIt('2');
		$instance->doIt('helt i hegnet');
	
		$mock2->once()->doIt('2');
		$mock->once()->doIt('1');
	}

	/**
	 * @expectedException MockitVerificationException
     * @Test
     */
	public function testRecursiveMockWithFailingVerification()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->with()->addDummy($this->any())->when()->doIt($this->any())->thenReturn('wah');
		
		Assert::equals('wah',$instance->addDummy($instance)->doIt('1'));
		
		$mock->with()->addDummy($this->any())->once()->doIt('2');
	}
	
	/**
	 * @expectedException Exception
     * @Test
     */
	public function testRecursiveMockOfMethodThatReturnsArrayThrowsExplainingException()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();

		$mock->with()->getDummies()->doSomething();
	}
	
	/**
	 * @expectedException Exception
     * @Test
     */
	public function testRecursiveMockOfMethodThatReturnsUnmockableObjectThrowsExplainingException()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();

		$mock->with()->doIt('1')->doSomething();
	}

	/**
	 * @expectedException MockitVerificationException
     * @Test
     */
	public function test_noFurtherInvocations_throwExceptionIfThereIsAnInvocation()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();

		$mock->instance()->getDummy();

		$mock->noFurtherInvocations();
	}

	/**
	 * @expectedException MockitVerificationException
     * @Test
     */
	public function test_noFurtherInvocations_throwExceptionIfThereIsFurtherInvocations()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();

		$mock->instance()->getDummy();
		$mock->instance()->getDummy();

		$mock->invoked()->getDummy();
		$mock->noFurtherInvocations();
	}

	/**
	 * @expectedException MockitVerificationException
     * @Test
     */
	public function test_callingSameMockMethodTwiceWhenRestrictedToOnceFails()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();

		$mock->instance()->getDummy();
		$mock->instance()->getDummy();

		$mock->once()->getDummy();
	}
}



