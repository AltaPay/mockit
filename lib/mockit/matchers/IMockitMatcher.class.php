<?php

interface IMockitMatcher
{
	function matches($other);
	
	function matchDescription($other);
	
	function description();
}