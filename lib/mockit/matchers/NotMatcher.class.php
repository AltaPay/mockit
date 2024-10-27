<?php

class NotMatcher
	implements IMockitMatcher
{
	/**
	 * @var IMockitMatcher
	 */
	private $childMatcher;
	
	public function __construct(IMockitMatcher $childMatcher)
	{
		$this->childMatcher = $childMatcher;
	}
	
	function matches($other)
	{
		return !$this->childMatcher->matches($other);
	}
	
	function matchDescription($other)
	{
		return '!('.$this->childMatcher->matches($other).')';
	}
	
	function description()
	{
		return 'not '.$this->childMatcher->description();
	}
}