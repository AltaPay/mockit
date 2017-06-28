<?php

/**
 * @method MyDummy myDummyWithTypeDefinitionOnClassLevel
 */
class MyDummy
	extends MyDummyBase
	implements IDummy
{
	/**
	 * @return string
	 */
	public function doIt($toWho)
	{
		return $toWho."'s momma";
	}

	/**
	 * @return MyDummy
	 */
	public function getDummy()
	{
		return new MyDummy();
	}
	
	/**
	 * @return array of MyDummy
	 */
	public function getDummies()
	{
		return array(new MyDummy(), new MyDummy());
	}
	
	
	/**
	 * @return MyDummy
	 */
	public function addDummy(MyDummy $dummy)
	{
		return $dummy;
	}

	public function addIDummy(IDummy $dummy)
	{
		return $dummy;
	}

	public function multipleArguments($arg1, $arg2)
	{
		
	}
	
	public function addValueObject(ValueObject $valueObject)
	{
		
	}
}