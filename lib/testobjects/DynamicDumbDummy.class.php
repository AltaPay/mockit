<?php

/**
 * @method MyDummy beDynamicAndAlsoDumb
 */
class DynamicDumbDummy
	extends Base
{
	public function __call($name, $args)
	{
		return new MyDummy();
	}
}
