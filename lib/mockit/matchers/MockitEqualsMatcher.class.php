<?php

class MockitEqualsMatcher
	implements IMockitMatcher
{
	private $value;
	
	public function __construct($value)
	{
		$this->value = $value;
	}
	
	public function matches($other)
	{
		return $this->value == $other;
	}
	
	function matchDescription($other)
	{
		if(is_object($this->value) || is_array($this->value))
		{
			$thisValue = explode("\n",print_r($this->value, true));
			$otherValue = explode("\n",print_r($other, true));
			if($this->matches($other))
			{
				return is_object($this->value) ? 'object matches' : 'array matches';
			}
			else
			{
				$missing = array_diff($thisValue, $otherValue);
				$added = array_diff($otherValue, $thisValue);
				return $this->prettyPrintDiff($missing, $added);
			}
		}
		else
		{
			if($this->matches($other))
			{
				return $this->getPrintableValue($this->value) .' == '.$this->getPrintableValue($other);
			}
			else 
			{
				return $this->getPrintableValue($this->value) .' != '.$this->getPrintableValue($other);
			}
		}
	}
	
	private function getPrintableValue($value)
	{
		if (is_null($value))
		{	
			return 'NULL';
		} 
		else 
		{
			return empty($value) ? '""' : $value;
		}
	}
	
	private function prettyPrintDiff($missing, $added)
	{
		$result = "\n";
		$keys = array_unique(array_merge(array_keys($missing), array_keys($added)));
		foreach($keys as $key)
		{
			if(isset($missing[$key]))
			{
				$result .= '-'.$missing[$key]."\n";
			}
			if(isset($added[$key]))
			{
				$result .= '+'.$added[$key]."\n";
			}
		}
		return $result;
	}
	
	public function description()
	{
		return Mockit::describeArgument($this->value);
	}
}