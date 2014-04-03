<?php
require dirname(__FILE__).'/../autoload.php';

class MockFailingTests
	extends MockitTestCase
{

	
	public function testFailingPassMultipleArguments()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$instance->multipleArguments('arg3','arg2');
	
		$mock->once()->multipleArguments('arg1','arg2');
	}
	
	public function testFailingPassMultipleArgumentsWithAnyMatch()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$instance->multipleArguments('arg3','arg2');
	
		$mock->once()->multipleArguments('arg1',$this->any());
	}
	
	
	public function testSameMatcherThatFails()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$dummy = new MyDummy();
		$otherDummy = new MyDummy();
	
		$instance->addDummy($otherDummy);
	
		$mock->once()->addDummy($this->same($dummy));
	}
	
	public function testSameMatcherWithMockThatFails()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$instance2 = $this->getMockit('MyDummy')->instance();
	
		$instance->addDummy($this->getMockit('MyDummy')->instance());
	
		$mock->once()->addDummy($this->same($instance2));
	}
	
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
	
	public function testInOrderVerificationsSimpleFailing()
	{
		$mock = $this->getMockit('MyDummy','dummy1');
		$instance = $mock->instance();
		$mock2 = $this->getMockit('MyDummy','dummy2');
		$instance2 = $mock2->instance();
		
		$instance->doIt('1');
		$instance2->doIt('2');
		
		$mock2->once()->doIt('2');
		$mock->once()->doIt('1');
	}
	
	public function testInOrderVerificationsMultipleIrrelevantCalls()
	{
		$mock = $this->getMockit('MyDummy','dummy1');
		$instance = $mock->instance();
		$mock2 = $this->getMockit('MyDummy','dummy2');
		$instance2 = $mock2->instance();
	
		$instance2->doIt('noget tredje');
		$instance->doIt('1');
		$instance->doIt('noget andet');
		$instance2->doIt('2');
		$instance->doIt('helt i hegnet');
	
		$mock2->once()->doIt('2');
		$mock->once()->doIt('1');
	}
	
	public function testDefaultMockUniqueIdsIsTestCaseClassNameAndLineNumberBased()
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
	
	public function testRecursiveMockWithFailingVerification()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$instance->getDummy()->doIt('1');
		
		$mock->with()->getDummy()->once()->doIt('2');
	}
	
	public function testRecursiveMockWithFailingInOrderVerification()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		$mock2 = $this->getMockit('MyDummy')->recursive();
		$instance2 = $mock2->instance();
		
		$instance->getDummy()->doIt('2');
		$instance2->getDummy()->doIt('1');
		
		$mock2->with()->getDummy()->once()->doIt('1');
		$mock->with()->getDummy()->once()->doIt('2');
	}
}



