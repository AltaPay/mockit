<?php

/**
* @method MyDummy beDynamic
 */
class DynamicDummy
	extends Base
{
	public function __call($name, $args)
	{
		return new MyDummy();
	}
}