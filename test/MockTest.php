<?php

require dirname(__FILE__).'/../autoload.php';

class MockTest
	extends MockitTestCase
{

	public function testPassMockObjectToMockObject()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
		
		$mock2 = $this->getMock('MyDummy');
		$instance2 = $mock2->instance();
		
		$instance->addDummy($instance2);
		
		$mock->once()->addDummy($instance2);
	}
	
	public function testPassMockToMockInterface()
	{
		$mock = $this->getMock('IDummy');
		$instance = $mock->instance();
	
		$mock2 = $this->getMock('MyDummy');
		$instance2 = $mock2->instance();
	
		$instance->addDummy($instance2);
	
		$mock->once()->addDummy($instance2);
	}
	
	public function testPassMockInterfaceToMock()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
		$mock2 = $this->getMock('IDummy');
		$instance2 = $mock2->instance();
	
		$instance->addIDummy($instance2);
	
		$mock->once()->addIDummy($instance2);
	}
	
	public function testAnythingMatches()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
		
		$instance->doIt('asdf');
		
		$mock->once()->doIt($this->any());
	}
	
	public function testAnyNumberOfCorrectInvocations()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance->doIt('asdf');
		$instance->doIt('asdf');
		$instance->doIt('asdf');
		$instance->doIt('asdf');
	
		$mock->any()->doIt('asdf');
	}
	
	public function testPassMultipleArguments()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
		
		$instance->multipleArguments('arg1','arg2');
		
		$mock->once()->multipleArguments('arg1','arg2');
	}
	
	public function testStubbing()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();

		$mock->when()->doIt('hat')->thenReturn('din mors hat');
		
		$this->assertEquals('din mors hat', $instance->doIt('hat'));
	}
	
	public function testSameMatcher()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
		
		$dummy = new MyDummy();
		
		$instance->addDummy($dummy);
		
		$mock->once()->addDummy($this->same($dummy));
	}
	
	public function testSameMatcherWithMock()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance2 = $this->getMock('MyDummy')->instance();
	
		$instance->addDummy($instance2);
	
		$mock->once()->addDummy($this->same($instance2));
	}
	
	public function testObjectEqualsMatching()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
		
		$obj1 = new ValueObject();
		$obj1->setProperty('prop1');
		
		$obj2 = new ValueObject();
		$obj2->setProperty('prop1'); 
		
		$instance->addValueObject($obj1);
		
		$mock->once()->addValueObject($obj2);
	}
	
	public function testStubbingWithDifferentParameters()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();

		$mock->when()->doIt('hat')->thenReturn('din mors hat');
		$mock->when()->doIt('kasket')->thenReturn('din mors kasket');
	
		$this->assertEquals('din mors hat', $instance->doIt('hat'));
		$this->assertEquals('din mors kasket', $instance->doIt('kasket'));
	}
	
	public function testStubbingWithAnyMatcher()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
	
		$mock->when()->doIt($this->any())->thenReturn('din mors hat');
	
		$this->assertEquals('din mors hat', $instance->doIt('hat'));
		$this->assertEquals('din mors hat', $instance->doIt('kasket'));
	}
	
	public function testStubbingMultipleStubActions()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
	
		$mock->when()->doIt($this->any())
			->thenReturn('din mors hat')
			->thenReturn('din mors anden hat')
			->thenThrow(new Exception('din mors exception'));
	
		$this->assertEquals('din mors hat', $instance->doIt('hat'));
		$this->assertEquals('din mors anden hat', $instance->doIt('kasket'));
		$exceptionThrown = false;
		try
		{
			$instance->doIt('wah');
		}
		catch(Exception $ex)
		{
			$exceptionThrown = true;
			$this->assertEquals('din mors exception', $ex->getMessage());
		}
		$this->assertTrue($exceptionThrown);
	}
	
	public function testLastStubbingActionContinues()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
		$mock->when()->doIt($this->any())
			->thenReturn('1')
			->thenReturn('2')
			->thenReturn('3');
	
		$this->assertEquals('1', $instance->doIt('hat'));
		$this->assertEquals('2', $instance->doIt('kasket'));
		$this->assertEquals('3', $instance->doIt('kasket'));
		$this->assertEquals('3', $instance->doIt('kasket'));
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testStubbingThrows()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
		
		$mock->when()->doIt($this->any())->thenThrow(new Exception(''));
		
		$instance->doIt('anything');
	}
	
	public function testStubOverride()
	{
		$mock = $this->getMock('MyDummy');
		$instance = $mock->instance();
	
		$mock->when()->doIt($this->any())->thenReturn('1');
		
		$mock->when()->doIt($this->any())->thenReturn('2');
	
		$this->assertEquals('2', $instance->doIt('hat'));
	}
}



