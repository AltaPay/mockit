<?php

class ValueObject
{
	private $property;
	
	public function setProperty($property)
	{
		$this->property = $property;
	}
	
	public function getProperty()
	{
		return $this->property;
	}
}