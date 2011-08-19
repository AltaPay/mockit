<?php

class PensioClassAutoLoader
{
	private static $allClassfiles;
	
	private static function getAllClassfiles()
	{
		if(!isset(self::$allClassfiles))
		{
			self::$allClassfiles = self::getClassfilesRecursively((dirname(__FILE__)).'/lib');
		}
		
		return self::$allClassfiles;
	}
	
	private static function getClassfilesRecursively($dir)
	{
		$result = array();
		$dp = opendir($dir);
		while(($file = readdir($dp)) !== false)
		{
			if(substr($file, 0, 1) != ".")
			{
				if(is_dir("$dir/$file"))
				{
					$result = array_merge($result, self::getClassfilesRecursively("$dir/$file"));
				}
				else if(preg_match('/([A-Za-z][A-Za-z0-9_]+)\.class\.php/', $file, $matches))
				{
					$result[$matches[1]] = "$dir/$file";
				}
			}
		}
		return $result;
	}
	
	public static function autoloadClass($className)
	{
		$classes = self::getAllClassfiles();
		if(isset($classes[$className]))
		{
			require_once $classes[$className];
		}
		else
		{
			throw new Exception("Could not load ".$className/*." from ".print_r($classes, true)*/); // I commented out the print_r because it was way too big to get any good info out of
		}
	}
}

spl_autoload_register('PensioClassAutoLoader::autoloadClass');