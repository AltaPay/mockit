<?php
require_once dirname(__FILE__).'/../autoload.php';

class MockFailingTests
    extends MockitTestCase
{
	// These tests are made for failing. Failing is what they'll do...

    /**
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
     * @Test
     */
	public function testInOrderVerificationsSimpleFailing()
	{
		$mock = Mockit::getMock('MyDummy','dummy1');
		$instance = $mock->instance();
		$mock2 = Mockit::getMock('MyDummy','dummy2');
		$instance2 = $mock2->instance();
		
		$instance->doIt('1');
		$instance2->doIt('2');
		
		$mock2->once()->doIt('2');
		$mock->once()->doIt('1');
	}

    /**
     * @Test
     */
	public function testInOrderVerificationsMultipleIrrelevantCalls()
	{
		$mock = Mockit::getMock('MyDummy','dummy1');
		$instance = $mock->instance();
		$mock2 = Mockit::getMock('MyDummy','dummy2');
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
     * @Test
     */
	public function testDefaultMockUniqueIdsIsTestCaseClassNameAndLineNumberBased()
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
     * @Test
     */
	public function testRecursiveMockWithFailingVerification()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$instance->getDummy()->doIt('1');
		
		$mock->with()->getDummy()->once()->doIt('2');
	}

    /**
     * @Test
     */
	public function testRecursiveMockWithFailingInOrderVerification()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		$mock2 = Mockit::getMock('MyDummy')->recursive();
		$instance2 = $mock2->instance();
		
		$instance->getDummy()->doIt('2');
		$instance2->getDummy()->doIt('1');
		
		$mock2->with()->getDummy()->once()->doIt('1');
		$mock->with()->getDummy()->once()->doIt('2');
	}
}



