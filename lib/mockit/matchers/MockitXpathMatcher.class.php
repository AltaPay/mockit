<?php

class MockitXpathMatcher
	implements IMockitMatcher
{
	private $xpath;
	private $value;

	public function __construct($xpath, $value)
	{
		$this->xpath = $xpath;
		$this->value = $value;
	}


	private function xpathElementFound($other)
	{
		$simpleXml = @simplexml_load_string($other);

		if($simpleXml === false)
		{
			return false;
		}

		$result = $simpleXml->xpath($this->xpath);

		if(count($result) == 0)
		{
			return false;
		}
		return $result;
	}

	function matches($other)
	{
		$result = $this->xpathElementFound($other);

		return (((string)$result[0]) == $this->value);
	}

	function matchDescription($other)
	{
		if($this->xpathElementFound($other) === false)
		{
			return 'Could not find element with xpath: '.$this->xpath.' in "'.$other.'"';
		}
		else if(!$this->matches($other))
		{
			$result = $this->xpathElementFound($other);

			return 'xpath element found, but '.((string)$result[0]) .' != '.$this->value;
		}
		else
		{
			return $this->description().' matched "'.$other.'"';
		}
	}

	function description()
	{
		return $this->xpath .' == '.$this->value;
	}
}