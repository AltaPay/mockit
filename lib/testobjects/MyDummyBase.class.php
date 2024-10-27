<?php


/**
 * @method MyDummy myDummyWithTypeDefinitionOnBaseClassLevel
 */
class MyDummyBase
	extends Base
{
	public function myDummyWithTypeDefinitionOnClassLevel()
	{
		return new MyDummy();
	}
}