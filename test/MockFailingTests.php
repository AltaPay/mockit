<?php
require dirname(__FILE__).'/../autoload.php';

class MockFailingTests
	extends MockitTestCase
{
/*
	public function testAnyNumberOfCorrectInvocationsButOneIncorrect()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance->doIt('asdf');
		$instance->doIt('asdf');
		$instance->doIt('asdf2');
		$instance->doIt('asdf');
	
		$mock->any()->doIt('asdf');
	}
	
	public function testFailingPassMultipleArguments()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance->multipleArguments('arg3','arg2');
	
		$mock->once()->multipleArguments('arg1','arg2');
	}
	
	public function testFailingPassMultipleArgumentsWithAnyMatch()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance->multipleArguments('arg3','arg2');
	
		$mock->once()->multipleArguments('arg1',$this->any());
	}
	
	
	public function testSameMatcherThatFails()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
		$dummy = new MyDummy();
		$otherDummy = new MyDummy();
	
		$instance->addDummy($otherDummy);
	
		$mock->once()->addDummy($this->same($dummy));
	}
	
	public function testSameMatcherWithMockThatFails()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance2 = $this->getMock('MyDummy')->instance();
	
		$instance->addDummy($this->getMock('MyDummy')->instance());
	
		$mock->once()->addDummy($this->same($instance2));
	}
	
	public function testObjectEqualsMatchingFailing()
	{
		$mock = $this->getMock('MyDummy');
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
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance(); /* @var $instance MyDummy * /
	
		$obj1 = new ValueObject();
		$obj1->setProperty('prop1');
	
		$obj2 = new ValueObject();
		$obj2->setProperty('prop2');
	
		$instance->multipleArguments($obj1, $obj2);
	
		$mock->once()->multipleArguments($obj2, $obj2);
	}
	
	*/
	public function testInOrderVerificationsSimpleFailing()
	{
		$inOrder = $this->getInOrder();
		$mock = $this->getMock('MyDummy');
		$mock->setInOrder($inOrder);
		$instance = $mock->instance();
		$mock2 = $this->getMock('MyDummy');
		$instance2 = $mock2->instance();
		$mock2->setInOrder($inOrder);
		
		$instance->doIt('1');
		$instance2->doIt('2');
		
		$mock2->once()->doIt('2');
		$mock->once()->doIt('1');
		$inOrder->verify();
	}
	
	public function testInOrderVerificationsMultipleIrrelevantCalls()
	{
		$inOrder = $this->getInOrder();
		$mock = $this->getMock('MyDummy');
		$mock->setInOrder($inOrder);
		$instance = $mock->instance();
		$mock2 = $this->getMock('MyDummy');
		$instance2 = $mock2->instance();
		$mock2->setInOrder($inOrder);
	
		$instance2->doIt('noget tredje');
		$instance->doIt('1');
		$instance->doIt('noget andet');
		$instance2->doIt('2');
		$instance->doIt('helt i hegnet');
	
		$mock2->once()->doIt('2');
		$mock->once()->doIt('1');
		$inOrder->verify();
	}
	
	public function testInOrderStrictVerifications()
	{
		$inOrder = $this->getInOrder(true);
		$mock = $this->getMock('MyDummy');
		$mock->setInOrder($inOrder);
		$instance = $mock->instance();
		$mock2 = $this->getMock('MyDummy');
		$instance2 = $mock2->instance();
		$mock2->setInOrder($inOrder);
	
		$instance2->doIt('noget tredje');
		$instance2->doIt('2');
		$instance->doIt('1');
	
		$mock2->once()->doIt('2');
		$mock->once()->doIt('1');
		$inOrder->verify();
	}
}



