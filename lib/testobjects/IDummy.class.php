<?php

interface IDummy
{
	function doIt($toWho);
	function addDummy(MyDummy $dummy);
	function addIDummy(IDummy $dummy);
}
