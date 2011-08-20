<?php

require dirname(__FILE__).'/../autoload.php';

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
}

interface IDummy
{
	function doIt($toWho);
	function addDummy(MyDummy $dummy);
	function addIDummy(IDummy $dummy);
}

$otherDummy = new MyDummy();

$mock = new Mockit('MyDummy');
$instance = $mock->instance();

$mock3 = new Mockit('MyDummy');
$instance3 = $mock3->instance();

$mock2 = new Mockit('IDummy');
$instance2 = $mock2->instance();

$mock->when()->doIt('you')->thenReturn('Yay');

print $instance->doIt('you1')."\n";
print $instance->doIt('you2')."\n";
print $instance->doIt('you3')."\n";

$instance2->addDummy($otherDummy);
$otherDummy->addDummy($instance);

$mock->exactly(2)->doIt('you2');

$mock2->once()->addDummy($otherDummy);

