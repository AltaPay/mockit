<?php
$rootDir =__DIR__;

require $rootDir.'/lib/AutoLoader.class.php';

AutoLoader::autoload(
	array(
		$rootDir.'/lib/'
		, $rootDir.'/vendor/pensio/testit/lib/'
		, $rootDir.'/vendor/pensio/mockit/lib/'
		, $rootDir.'/tests/lib/'
	)
	, null
);

if(isset($argv[1]) && isset($argv[2]) && is_file($argv[1]))
{
	$testlist = TestitTestlist::singleTest($argv[1],$argv[2]);
}
else if(isset($argv[1]) && is_file($argv[1]))
{
	$testlist = TestitTestlist::file($argv[1]);
}
else
{
	$testlist = TestitTestlist::files($rootDir.'/test/**/*Test.php');
}

$logger = new TestitConsoleLogger();
$runner = new TestitRunner(
	$testlist
	, $logger
	, array('MockitVerificationException')
	, null
	, function (){ Mockit::resetMocks(); }
);

$runner->runAll();

$logger->writeReport();

if(!$logger->wasSuccessful())
{
	exit(-1);
}