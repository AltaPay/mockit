<?php

require_once dirname(__FILE__).'/../autoload.php';

class MockTest
    extends MockitTestCase
{
	/**
     * @Test
     */
	public function testPassMockObjectToMockObject()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		
		$mock2 = Mockit::getMock('MyDummy');
		$instance2 = $mock2->instance();
		
		$instance->addDummy($instance2);

		$mock->once()->addDummy($instance2);
	}
	
	/**
     * @Test
     */
	public function testPassMockToMockInterface()
	{
		$mock = Mockit::getMock('IDummy');
		$instance = $mock->instance();
	
		$mock2 = Mockit::getMock('MyDummy');
		$instance2 = $mock2->instance();
	
		$instance->addDummy($instance2);
	
		$mock->once()->addDummy($instance2);
	}
	
	/**
     * @Test
     */
	public function testPassMockInterfaceToMock()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
		$mock2 = Mockit::getMock('IDummy');
		$instance2 = $mock2->instance();
	
		$instance->addIDummy($instance2);
	
		$mock->once()->addIDummy($instance2);
	}
	
	/**
     * @Test
     */
	public function testAnythingMatches()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		
		$instance->doIt('asdf');
		
		$mock->once()->doIt($this->any());
	}
	
	/**
     * @Test
     */
	public function testAnyNumberOfCorrectInvocations()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance->doIt('asdf');
		$instance->doIt('asdf');
		$instance->doIt('asdf');
		$instance->doIt('asdf');
	
		$mock->any()->doIt('asdf');
	}
	
	/**
     * @Test
     */
	public function testPassMultipleArguments()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		
		$instance->multipleArguments('arg1','arg2');
		
		$mock->once()->multipleArguments('arg1','arg2');
	}
	
	/**
     * @Test
     */
	public function testStubbing()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();

		$mock->when()->doIt('hat')->thenReturn('din mors hat');

		Assert::equals('din mors hat', $instance->doIt('hat'));
	}
	
	/**
     * @Test
     */
	public function testSameMatcher()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		
		$dummy = new MyDummy();
		
		$instance->addDummy($dummy);
		
		$mock->once()->addDummy($this->same($dummy));
	}
	
	/**
     * @Test
     */
	public function testSameMatcherWithMock()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance2 = Mockit::getMock('MyDummy')->instance();
	
		$instance->addDummy($instance2);
	
		$mock->once()->addDummy($this->same($instance2));
	}
	
	/**
     * @Test
     */
	public function testObjectEqualsMatching()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		
		$obj1 = new ValueObject();
		$obj1->setProperty('prop1');
		
		$obj2 = new ValueObject();
		$obj2->setProperty('prop1'); 
		
		$instance->addValueObject($obj1);
		
		$mock->once()->addValueObject($this->equals($obj2));
	}
	
	/**
     * @Test
     */
	public function testStubbingWithDifferentParameters()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();

		$mock->when()->doIt('hat')->thenReturn('din mors hat');
		$mock->when()->doIt('kasket')->thenReturn('din mors kasket');
	
		Assert::equals('din mors hat', $instance->doIt('hat'));
		Assert::equals('din mors kasket', $instance->doIt('kasket'));
	}
	
	/**
     * @Test
     */
	public function testStubbingWithAnyMatcher()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
	
		$mock->when()->doIt($this->any())->thenReturn('din mors hat');
	
		Assert::equals('din mors hat', $instance->doIt('hat'));
		Assert::equals('din mors hat', $instance->doIt('kasket'));
	}
	
	/**
     * @Test
     */
	public function testStubbingMultipleStubActions()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
	
		$mock->when()->doIt($this->any())
			->thenReturn('din mors hat')
			->thenReturn('din mors anden hat')
			->thenThrow(new Exception('din mors exception'));
	
		Assert::equals('din mors hat', $instance->doIt('hat'));
		Assert::equals('din mors anden hat', $instance->doIt('kasket'));
		$exceptionThrown = false;
		try
		{
			$instance->doIt('wah');
		}
		catch(Exception $ex)
		{
			$exceptionThrown = true;
			Assert::equals('din mors exception', $ex->getMessage());
		}
		Assert::true($exceptionThrown);
	}
	
	/**
     * @Test
     */
	public function testLastStubbingActionContinues()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
		$mock->when()->doIt($this->any())
			->thenReturn('1')
			->thenReturn('2')
			->thenReturn('3');
	
		Assert::equals('1', $instance->doIt('hat'));
		Assert::equals('2', $instance->doIt('kasket'));
		Assert::equals('3', $instance->doIt('kasket'));
		Assert::equals('3', $instance->doIt('kasket'));
	}
	
	/**
	 * @expectedException Exception
     * @Test
     */
	public function testStubbingThrows()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		
		$mock->when()->doIt($this->any())->thenThrow(new Exception(''));
		
		$instance->doIt('anything');
	}
	
	/**
     * @Test
     */
	public function testStubOverride()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
		$mock->when()->doIt($this->any())->thenReturn('1');
		
		$mock->when()->doIt($this->any())->thenReturn('2');
	
		Assert::equals('2', $instance->doIt('hat'));
	}
	
	/**
     * @Test
     */
	public function testInOrderVerifications()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		$mock2 = Mockit::getMock('MyDummy');
		$instance2 = $mock2->instance();
	
		$instance->doIt('1');
		$instance2->doIt('2');
	
		$mock->once()->doIt('1');
		$mock2->once()->doIt('2');
	}

	/**
     * @Test
     */
	public function testInOrderVerificationOfSameMethod()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
		$instance->doIt('1');
		$instance->doIt('2');
	
		$mock->once()->doIt('1');
		$mock->once()->doIt('2');
	}
	
	/**
     * @Test
     */
	public function testInOrderVerificationOfSameMethodSandwhichingOtherMethod()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		$mock2 = Mockit::getMock('MyDummy');
		$instance2 = $mock2->instance();
	
		$instance->doIt('1');
		$instance2->doIt('2');
		$instance->doIt('1');
	
		$mock->any()->doIt('1');
		$mock2->any()->doIt('2');
		$mock->any()->doIt('1');
	}

	/**
     * @Test
     */
	public function testOutOfOrderVerifications()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
		$mock2 = Mockit::getMock('MyDummy')->outOfOrder();
		$instance2 = $mock2->instance();
	
		$instance2->doIt('2');
		$instance->doIt('1');
		
		$mock->once()->doIt('1');
		$mock2->once()->doIt('2');
	}
	
	/**
     * @Test
     */
	public function testRecursiveMockWithStubbingAndVerification()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->with()->getDummy()->when()->doIt('1')->thenReturn('lol');
		
		Assert::equals('lol',$instance->getDummy()->doIt('1'));
		$mock->with()->getDummy()->once()->doIt('1');
	}
	
	/**
     * @Test
     */
	public function testRecursiveMockWithStubbing()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->with()->getDummy()->when()->doIt('1')->thenReturn('lol');
		
		Assert::equals('lol',$instance->getDummy()->doIt('1'));
	}
	
	/**
     * @Test
     */
	public function testRecursiveMockWithVerification()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$instance->getDummy()->doIt('1');
		
		$mock->with()->getDummy()->once()->doIt('1');
	}
	
	/**
     * @Test
     */
	public function testOverrideRecursiveMock()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->when()->getDummy()->thenReturn(new MyDummy());
		
		Assert::equals("1's momma", $instance->getDummy()->doIt('1'));
	}

	/**
     * @Test
     */
	public function testRecursiveMockOfArrayReturnsEmptyArray()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		
		Assert::equals(array(),$instance->getDummies());
	}
	
	/**
     * @Test
     */
	public function testRecursiveMockOfUnmockableClassReturnsNull()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		
		Assert::null($instance->doIt('1'));
	}

	/**
     * @Test
     */
	public function testRecursiveMockWithSpecificOverriding()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->with()->addDummy($this->any())->when()->doIt($this->any())->thenReturn('buh');
		$mock->with()->addDummy($instance)->when()->doIt($this->any())->thenReturn('wah');

		Assert::equals("wah",$instance->addDummy($instance)->doIt('1'));
	}

	/**
     * @Test
     */
	public function testOnRecursiveMocksUseMatchingChildMock()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$instance = $mock->instance();
		
		$mock->with()->addDummy($this->any())->when()->doIt($this->any())->thenReturn('buh');
		$mock->with()->addDummy($instance)->when()->doIt($this->any())->thenReturn('wah');

		Assert::equals("buh",$instance->addDummy(new MyDummy())->doIt('1'));
	}
	
	/**
     * @Test
     */
	public function testStubbingWhileSettingUpStubBeforeLastMockIsCreated()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();
	
	
		$mock->when()->doIt($this->any())->thenReturn('din mors hat');
	
		$mock2 = Mockit::getMock('MyDummy');
		$instance2 = $mock2->instance();
	
	
		$mock2->when()->doIt($this->any())->thenReturn('din mors hat2');
		
		Assert::equals('din mors hat', $instance->doIt('whatever'));
		Assert::equals('din mors hat2', $instance2->doIt('whatever'));
	}



	/**
     * @Test
     */
	public function test_noFurtherInvocations_successWhenNoInteractions()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();
		$mock->instance();

		$mock->noFurtherInvocations();
	}

	/**
     * @Test
     */
	public function test_noFurtherInvocations_successWhenNoInteractionsAfterMatchedInteractions()
	{
		$mock = Mockit::getMock('MyDummy')->recursive();

		$mock->instance()->getDummy();

		$mock->once()->getDummy();
		$mock->noFurtherInvocations();
	}

	/**
     * @Test
     */
	public function test_matchTheAnyMatcherIfAMoreSpecificMatchIsNotAvailable()
	{
		$mock = Mockit::getMock('MyDummy','testmock')->recursive();
		$instance = $mock->instance();

		$mock->with()->addDummy($instance)->when()->doIt($this->any())->thenReturn('wah');
		$mock->with()->addDummy($this->any())->when()->doIt($this->any())->thenReturn('buh');

		Assert::equals('testmock->addDummy(*anything*)', Mockit::uniqueid($instance->addDummy(new MyDummy())));
	}

	/**
     * @Test
     */
	public function test_matchTheSpecificMatchIfAvailable()
	{
		$mock = Mockit::getMock('MyDummy','testmock')->recursive();
		$instance = $mock->instance();

		$mock->with()->addDummy($this->any())->when()->doIt($this->any())->thenReturn('buh');
		$mock->with()->addDummy($instance)->when()->doIt($this->any())->thenReturn('wah');

		Assert::equals('testmock->addDummy(testmock)', Mockit::uniqueid($instance->addDummy($instance)));
	}

	/**
     * @Test
     */
	public function testAnyNumberOfCorrectInvocationsButOneIncorrect()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();

		$instance->doIt('asdf');
		$instance->doIt('asdf');
		$instance->doIt('asdf2');
		$instance->doIt('asdf');

		$mock->any()->doIt('asdf');
	}

	/**
     * @Test
     */
	public function test_invokingTheSameMethodTwiceWithDifferentArgumentsIsMatchedCorrectlyWithInvoked()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();

		$instance->doIt('hat1');
		$instance->doIt('hat2');

		$mock->invoked()->doIt('hat1');
		$mock->invoked()->doIt('hat2');
	}

	/**
     * @Test
     */
	public function test_invokingTheSameMethodTwiceWithDifferentArgumentsIsMatchedCorrectlyWithOnce()
	{
		$mock = Mockit::getMock('MyDummy');
		$instance = $mock->instance();

		$instance->doIt('hat1');
		$instance->doIt('hat2');

		$mock->once()->doIt('hat1');
		$mock->once()->doIt('hat2');
	}

	/**
     * @Test
     */
	public function test_recursiveMockUnderstandsClassLevelTypeDeclarations()
	{
		$mock = Mockit::getMock('MyDummy')->recursive(); /* @var $mock Mock_MyDummy */
		$instance = $mock->instance(); /* @var $instance MyDummy */

		$instance->myDummyWithTypeDefinitionOnClassLevel()->doIt('who');

		$mock->with()->myDummyWithTypeDefinitionOnClassLevel()->once()->doIt('who');
	}

	/**
     * @Test
     */
	public function test_recursiveMockUnderstandsBaseClassLevelTypeDeclarations()
	{
		$mock = Mockit::getMock('MyDummy')->recursive(); /* @var $mock Mock_MyDummy */
		$instance = $mock->instance(); /* @var $instance MyDummy */

		$instance->myDummyWithTypeDefinitionOnBaseClassLevel()->doIt('who');

		$mock->with()->myDummyWithTypeDefinitionOnBaseClassLevel()->once()->doIt('who');
	}

	/**
     * @Test
     */
	public function test_recursiveMockUnderstandsClassLevelTypeDeclarations_forDynamicMocks()
	{
		$mock = Mockit::getMock('DynamicDummy')->recursive()->dynamic(); /* @var $mock Mock_DynamicDummy */
		$instance = $mock->instance(); /* @var $instance DynamicDummy */

		$instance->beDynamic()->doIt('who');

		$mock->with()->beDynamic()->once()->doIt('who');
	}

	/**
     * @Test
     */
	public function test_recursiveMockDoesNotTruncateClassLevelDeclarations_forDynamicMocks()
	{
		$mock = Mockit::getMock('DynamicDumbDummy')->recursive()->dynamic(); /* @var $mock Mock_DynamicDumbDummy */
		$instance = $mock->instance(); /* @var $instance DynamicDummy */

		// Only beDynamicAndAlsoDumb() is declared on DynamicDumbDummy.class, so it should not be able to match beDynamic()
		// and thus the return value of beDynamic should be null
		Assert::null($instance->beDynamic());
	}
}



