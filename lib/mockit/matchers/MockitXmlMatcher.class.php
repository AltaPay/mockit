<?php

class MockitXmlMatcher
	implements IMockitMatcher
{
	private $xmlOrString;

	public function __construct($xmlOrString)
	{
		$this->xmlOrString = $xmlOrString;
	}

	function matches($other)
	{
		list($succeeded,$reason) = $this->testAndDescribe($other);
		return $succeeded;
	}

	function matchDescription($other)
	{
		list($succeeded,$reason) = $this->testAndDescribe($other);
		return $reason;
	}

	function description()
	{
		return $this->xmlOrString;
	}

	private function testAndDescribe($other)
	{
		$succeeded = true;
		$reason = '';

		if (!($other instanceof SimpleXMLElement))
		{
			$succeeded = false;
			$reason = 'Expected type of SimpleXMLElement, but got '.$this->getTypeOf($other);
		}
		else
		{
			$thisAsString = $this->getXmlAsString($this->xmlOrString);
			$otherAsString = $this->getXmlAsString($other);

			if (!$this->hasDoctype($thisAsString))
			{
				$otherAsString = $this->removeDoctype($otherAsString);
			}

			if ($thisAsString != $otherAsString)
			{
				$succeeded = false;
				$reason = $thisAsString .' != '. $otherAsString;
			}
		}

		return array($succeeded, $reason);
	}

	private function getTypeOf($var = null)
	{
		$type = gettype($var);

		if ($type == 'object')
		{
			return get_class($var);
		}

		return $type;
	}

	private function getXmlAsString($var)
	{
		$string = $var;
		if ($var instanceof SimpleXMLElement)
		{
			$string = $var->asXML();
		}

		return trim($string);
	}

	private function startsWith($str, $needle)
	{
		return substr($str, 0, strlen($needle)) == $needle;
	}

	private function hasDoctype($xmlString)
	{
		return $this->startsWith($xmlString, '<?xml');
	}

	private function removeDoctype($xmlString)
	{
		if ($this->hasDoctype($xmlString))
		{
			$pattern = "/\\<\\?xml\\s+.*\\?\\>[\r\n]*/i";
			return preg_replace($pattern, '', $xmlString, 1);
		}

		return $xmlString;
	}
}
