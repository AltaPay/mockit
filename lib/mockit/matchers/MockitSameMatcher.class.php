<?php

class MockitSameMatcher
	implements IMockitMatcher
{
	private $obj;
	
	public function __construct($obj)
	{
		$this->obj = $obj;
	}
	
	public function matches($other)
	{
		return $this->obj === $other;
	}
	
	function matchDescription($other)
	{
		if($this->matches($other))
		{
			return $this->uniqueid($this->obj) .' === '.$this->uniqueid($other);
		}
		else
		{
			return $this->uniqueid($this->obj) .' !== '.$this->uniqueid($other);
		}
	}
	
	function uniqueid($object) 
	{
		if (!is_object($object)) 
		{
			throw new Exception("Same matcher only works for objects");
		}

		if (!isset($object->__oid__))
		{
			$object->__oid__ = uniqid(get_class($object).'_');
		}
		return $object->__oid__;
	}
}