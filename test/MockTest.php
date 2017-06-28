<?php

require dirname(__FILE__).'/../autoload.php';

class MockTest
	extends MockitTestCase
{
	public function testPassMockObjectToMockObject()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		
		$mock2 = $this->getMockit('MyDummy');
		$instance2 = $mock2->instance();
		
		$instance->addDummy($instance2);

		$mock->once()->addDummy($instance2);
	}
	
	public function testPassMockToMockInterface()
	{
		$mock = $this->getMockit('IDummy');
		$instance = $mock->instance();
	
		$mock2 = $this->getMockit('MyDummy');
		$instance2 = $mock2->instance();
	
		$instance->addDummy($instance2);
	
		$mock->once()->addDummy($instance2);
	}
	
	public function testPassMockInterfaceToMock()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$mock2 = $this->getMockit('IDummy');
		$instance2 = $mock2->instance();
	
		$instance->addIDummy($instance2);
	
		$mock->once()->addIDummy($instance2);
	}
	
	public function testAnythingMatches()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		
		$instance->doIt('asdf');
		
		$mock->once()->doIt($this->any());
	}
	
	public function testAnyNumberOfCorrectInvocations()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$instance->doIt('asdf');
		$instance->doIt('asdf');
		$instance->doIt('asdf');
		$instance->doIt('asdf');
	
		$mock->any()->doIt('asdf');
	}
	
	public function testPassMultipleArguments()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		
		$instance->multipleArguments('arg1','arg2');
		
		$mock->once()->multipleArguments('arg1','arg2');
	}
	
	public function testStubbing()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();

		$mock->when()->doIt('hat')->thenReturn('din mors hat');
		
		$this->assertEquals('din mors hat', $instance->doIt('hat'));
	}
	
	public function testSameMatcher()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		
		$dummy = new MyDummy();
		
		$instance->addDummy($dummy);
		
		$mock->once()->addDummy($this->same($dummy));
	}
	
	public function testSameMatcherWithMock()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$instance2 = $this->getMockit('MyDummy')->instance();
	
		$instance->addDummy($instance2);
	
		$mock->once()->addDummy($this->same($instance2));
	}
	
	public function testObjectEqualsMatching()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		
		$obj1 = new ValueObject();
		$obj1->setProperty('prop1');
		
		$obj2 = new ValueObject();
		$obj2->setProperty('prop1'); 
		
		$instance->addValueObject($obj1);
		
		$mock->once()->addValueObject($this->equals($obj2));
	}
	
	public function testStubbingWithDifferentParameters()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();

		$mock->when()->doIt('hat')->thenReturn('din mors hat');
		$mock->when()->doIt('kasket')->thenReturn('din mors kasket');
	
		$this->assertEquals('din mors hat', $instance->doIt('hat'));
		$this->assertEquals('din mors kasket', $instance->doIt('kasket'));
	}
	
	public function testStubbingWithAnyMatcher()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
	
		$mock->when()->doIt($this->any())->thenReturn('din mors hat');
	
		$this->assertEquals('din mors hat', $instance->doIt('hat'));
		$this->assertEquals('din mors hat', $instance->doIt('kasket'));
	}
	
	public function testStubbingMultipleStubActions()
	{
		$mock = $this->getMockit('MyDummy');
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
		$mock = $this->getMockit('MyDummy');
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
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		
		$mock->when()->doIt($this->any())->thenThrow(new Exception(''));
		
		$instance->doIt('anything');
	}
	
	public function testStubOverride()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$mock->when()->doIt($this->any())->thenReturn('1');
		
		$mock->when()->doIt($this->any())->thenReturn('2');
	
		$this->assertEquals('2', $instance->doIt('hat'));
	}
	
	public function testInOrderVerifications()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		$mock2 = $this->getMockit('MyDummy');
		$instance2 = $mock2->instance();
	
		$instance->doIt('1');
		$instance2->doIt('2');
	
		$mock->once()->doIt('1');
		$mock2->once()->doIt('2');
	}

	public function testInOrderVerificationOfSameMethod()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
		$instance->doIt('1');
		$instance->doIt('2');
	
		$mock->once()->doIt('1');
		$mock->once()->doIt('2');
	}
	
	public function testInOrderVerificationOfSameMethodSandwhichingOtherMethod()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		$mock2 = $this->getMockit('MyDummy');
		$instance2 = $mock2->instance();
	
		$instance->doIt('1');
		$instance2->doIt('2');
		$instance->doIt('1');
	
		$mock->any()->doIt('1');
		$mock2->any()->doIt('2');
		$mock->any()->doIt('1');
	}

	public function testOutOfOrderVerifications()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
		$mock2 = $this->getMockit('MyDummy')->outOfOrder();
		$instance2 = $mock2->instance();
	
		$instance2->doIt('2');
		$instance->doIt('1');
		
		$mock->once()->doIt('1');
		$mock2->once()->doIt('2');
	}
	
	public function testRecursiveMockWithStubbingAndVerification()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->with()->getDummy()->when()->doIt('1')->thenReturn('lol');
		
		$this->assertEquals('lol',$instance->getDummy()->doIt('1'));
		$mock->with()->getDummy()->once()->doIt('1');
	}
	
	public function testRecursiveMockWithStubbing()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->with()->getDummy()->when()->doIt('1')->thenReturn('lol');
		
		$this->assertEquals('lol',$instance->getDummy()->doIt('1'));
	}
	
	public function testRecursiveMockWithVerification()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$instance->getDummy()->doIt('1');
		
		$mock->with()->getDummy()->once()->doIt('1');
	}
	
	public function testOverrideRecursiveMock()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->when()->getDummy()->thenReturn(new MyDummy());
		
		$this->assertEquals("1's momma", $instance->getDummy()->doIt('1'));
	}

	public function testRecursiveMockOfArrayReturnsEmptyArray()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$this->assertEquals(array(),$instance->getDummies());
	}
	
	public function testRecursiveMockOfUnmockableClassReturnsNull()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$this->assertNull($instance->doIt('1'));
	}

	public function testRecursiveMockWithSpecificOverriding()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->with()->addDummy($this->any())->when()->doIt($this->any())->thenReturn('buh');
		$mock->with()->addDummy($instance)->when()->doIt($this->any())->thenReturn('wah');

		$this->assertEquals("wah",$instance->addDummy($instance)->doIt('1'));
	}

	public function testOnRecursiveMocksUseMatchingChildMock()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->with()->addDummy($this->any())->when()->doIt($this->any())->thenReturn('buh');
		$mock->with()->addDummy($instance)->when()->doIt($this->any())->thenReturn('wah');

		$this->assertEquals("buh",$instance->addDummy(new MyDummy())->doIt('1'));
	}
	
	public function testStubbingWhileSettingUpStubBeforeLastMockIsCreated()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();
	
	
		$mock->when()->doIt($this->any())->thenReturn('din mors hat');
	
		$mock2 = $this->getMockit('MyDummy');
		$instance2 = $mock2->instance();
	
	
		$mock2->when()->doIt($this->any())->thenReturn('din mors hat2');
		
		$this->assertEquals('din mors hat', $instance->doIt('whatever'));
		$this->assertEquals('din mors hat2', $instance2->doIt('whatever'));
	}



	public function test_noFurtherInvocations_successWhenNoInteractions()
	{
		$mock = $this->getMockit('MyDummy')->recursive();
		$mock->instance();

		$mock->noFurtherInvocations();
	}

	public function test_noFurtherInvocations_successWhenNoInteractionsAfterMatchedInteractions()
	{
		$mock = $this->getMockit('MyDummy')->recursive();

		$mock->instance()->getDummy();

		$mock->once()->getDummy();
		$mock->noFurtherInvocations();
	}

	public function test_matchTheAnyMatcherIfAMoreSpecificMatchIsNotAvailable()
	{
		$mock = $this->getMockit('MyDummy','testmock')->recursive();
		$instance = $mock->instance();

		$mock->with()->addDummy($instance)->when()->doIt($this->any())->thenReturn('wah');
		$mock->with()->addDummy($this->any())->when()->doIt($this->any())->thenReturn('buh');

		$this->assertEquals('testmock->addDummy(*anything*)', Mockit::uniqueid($instance->addDummy(new MyDummy())));
	}

	public function test_matchTheSpecificMatchIfAvailable()
	{
		$mock = $this->getMockit('MyDummy','testmock')->recursive();
		$instance = $mock->instance();

		$mock->with()->addDummy($this->any())->when()->doIt($this->any())->thenReturn('buh');
		$mock->with()->addDummy($instance)->when()->doIt($this->any())->thenReturn('wah');

		$this->assertEquals('testmock->addDummy(testmock)', Mockit::uniqueid($instance->addDummy($instance)));
	}

	public function testAnyNumberOfCorrectInvocationsButOneIncorrect()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();

		$instance->doIt('asdf');
		$instance->doIt('asdf');
		$instance->doIt('asdf2');
		$instance->doIt('asdf');

		$mock->any()->doIt('asdf');
	}

	public function test_invokingTheSameMethodTwiceWithDifferentArgumentsIsMatchedCorrectlyWithInvoked()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();

		$instance->doIt('hat1');
		$instance->doIt('hat2');

		$mock->invoked()->doIt('hat1');
		$mock->invoked()->doIt('hat2');
	}

	public function test_invokingTheSameMethodTwiceWithDifferentArgumentsIsMatchedCorrectlyWithOnce()
	{
		$mock = $this->getMockit('MyDummy');
		$instance = $mock->instance();

		$instance->doIt('hat1');
		$instance->doIt('hat2');

		$mock->once()->doIt('hat1');
		$mock->once()->doIt('hat2');
	}

	public function test_recursiveMockUnderstandsClassLevelTypeDeclarations()
	{
		$mock = $this->getMockit('MyDummy')->recursive(); /* @var $mock Mock_MyDummy */
		$instance = $mock->instance(); /* @var $instance MyDummy */

		$instance->myDummyWithTypeDefinitionOnClassLevel()->doIt('who');

		$mock->with()->myDummyWithTypeDefinitionOnClassLevel()->once()->doIt('who');
	}

	public function test_recursiveMockUnderstandsBaseClassLevelTypeDeclarations()
	{
		$mock = $this->getMockit('MyDummy')->recursive(); /* @var $mock Mock_MyDummy */
		$instance = $mock->instance(); /* @var $instance MyDummy */

		$instance->myDummyWithTypeDefinitionOnBaseClassLevel()->doIt('who');

		$mock->with()->myDummyWithTypeDefinitionOnBaseClassLevel()->once()->doIt('who');
	}

	public function test_recursiveMockUnderstandsClassLevelTypeDeclarations_forDynamicMocks()
	{
		$mock = $this->getMockit('DynamicDummy')->recursive()->dynamic(); /* @var $mock Mock_DynamicDummy */
		$instance = $mock->instance(); /* @var $instance DynamicDummy */

		$instance->beDynamic()->doIt('who');

		$mock->with()->beDynamic()->once()->doIt('who');
	}

	public function test_recursiveMockDoesNotTruncateClassLevelDeclarations_forDynamicMocks()
	{
		$mock = $this->getMockit('DynamicDumbDummy')->recursive()->dynamic(); /* @var $mock Mock_DynamicDumbDummy */
		$instance = $mock->instance(); /* @var $instance DynamicDummy */

		// Only beDynamicAndAlsoDumb() is declared on DynamicDumbDummy.class, so it should not be able to match beDynamic()
		// and thus the return value of beDynamic should be null
		$this->assertNull($instance->beDynamic());
	}
}



