<?php
$baseDir = dirname(dirname(__DIR__));
$outputDir = __DIR__.'/cache/';

if (!file_exists($outputDir))
{
	mkdir($outputDir);
}

require_once __DIR__.'/mockit/MockitGenerator.class.php';
require_once __DIR__.'/mockit/MockHelperGenerator.class.php';

$generator = new MockHelperGenerator($outputDir);

$generator->resetMockHelperFile();

spl_autoload_register(function($class)
{
	require_once __DIR__.'/testobjects/'.$class.'.class.php';
});

foreach(glob(__DIR__.'/testobjects/*.class.php') as $file)
{
	if(!preg_match('/([^\/]+)\.class\.php$/',$file,$match))
	{
		throw new Exception('Could not find class name for file: '.$file);
	}
	$className = $match[1];
	try
	{
		if(!class_exists($className) && !interface_exists($className))
		{
			continue;
		}

		$generator->addMockHelperFor($className);
	}
	catch(Exception $e)
	{
		//print $classFile.": ".$e->__toString()."\n";
		// ignore errors, generate what we can
	}
}
