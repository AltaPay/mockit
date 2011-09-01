<?php

interface IMockit
{
	/**
	 * @return IMockit
	 */
	function outOfOrder();
	/**
	 * @return IMockit
	 */
	function recursive();
	
	function when();
	
	function any();
	
	function exactly($times);
	
	function once();
	
	function never();
	
	function instance();
	
	function with();
}