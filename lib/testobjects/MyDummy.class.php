<?php

class MyDummy
implements IDummy
{
	public function doIt($toWho)
	{
		return $toWho."'s momma";
	}

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