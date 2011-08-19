<?php

require dirname(__FILE__).'/../autoload.php';

class MyDummy
{
	public function doIt($toWho)
	{
		return $toWho."'s momma";
	}
	
	public function addDummy(MyDummy $dummy)
	{
		return $dummy;
	}
}

$otherDummy = new MyDummy();

$mock = new Mockit('MyDummy');
$instance = $mock->instance();

$mock->when()->doIt('you')->thenReturn('Yay');

print $instance->doIt('you')."\n";

$instance->addDummy($instance);

$mock->verify()->doIt('you')->once();

$mock->verify()->addDummy($otherDummy)->once();