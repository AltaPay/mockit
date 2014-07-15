<?php

class MockHelperGenerator
{
	private $cacheDirectory;

	public function __construct($cacheDirectory)
	{
		$this->cacheDirectory = $cacheDirectory;
	}

	public function resetMockHelperFile()
	{
		foreach(glob($this->cacheDirectory.'/*.php') as $mockHelperFile)
		{
			unlink($mockHelperFile);
		}
	}

	public function addMockHelpersFor($classes)
	{
		foreach($classes as $className)
		{
			$this->addMockHelperFor($className);
		}
	}

	public function addMockHelperFor($className)
	{
		$reflectionClass = new ReflectionClass($className);
		$code = "<?php\n";

		$code .= 'interface Mock_'.$className." \n{\n";
		$code .= "\t/**\n\t * @return Mock_implementation_".$className."\n\t */\n\tfunction when();\n\n";
		$code .= "\t/**\n\t * @return Mock_implementation_".$className."\n\t */\n\tfunction exactly(\$num);\n\n";
		$code .= "\t/**\n\t * @return Mock_implementation_".$className."\n\t */\n\tfunction once();\n\n";
		$code .= "\t/**\n\t * @return Mock_implementation_".$className."\n\t */\n\tfunction invoked();\n\n";
		$code .= "\t/**\n\t * @return Mock_implementation_".$className."\n\t */\n\tfunction never();\n\n";
		$code .= "\tfunction noFurtherInvocations();\n\n";
		$code .= "\t/**\n\t * @return ".$className."\n\t */\n\tfunction instance();\n\n";
		$code .= "\t/**\n\t * @return Mock_with_implementation_".$className."\n\t */\n\tfunction with();\n\n";
		$code .= "}\n";


		$implementationCode = 'interface Mock_implementation_'.$className." \n{\n";
		$withImplementationCode = 'interface Mock_with_implementation_'.$className." \n{\n";

		foreach($reflectionClass->getMethods() as $method) /* @var $method ReflectionMethod */
		{
			if($method->isConstructor())
			{
				continue;
			}

			$parameters = array();
			foreach($method->getParameters() as $parameter) /* @var $parameter ReflectionParameter */
			{
				$paramString = '';

				if($parameter->isArray())
				{
					$paramString .= 'array ';
				}
				else if(!is_null($parameter->getClass()))
				{
					$paramString .= $parameter->getClass()->getName().' ';
				}
				if($parameter->isPassedByReference())
				{
					$paramString .= '&';
				}
				$paramString .= '$'.$parameter->getName();

				$paramString .= ' = null';

				$parameters[] = $paramString;
			}

			if(
				preg_match('/\@return\s+(\S+)/',$method->getDocComment(), $matches)
				&& !in_array($matches[1], array('void','mixed','string'))
				&& (strpos($matches[1],'char') != 0)
				&& (strpos($matches[1],'varchar') != 0)
				&& (class_exists($matches[1]) || interface_exists($matches[1])))
			{
			{
				$withImplementationCode .= "\t/**\n\t *  @return Mock_".$matches[1]."\n\t */\n\tfunction ".$method->getName()."(".implode(',',$parameters).");\n\n";

			}
			$implementationCode .= "\t/**\n\t * @return MockitStub\n\t*/\n\tfunction ".$method->getName()."(".implode(',',$parameters).");\n\n";
		}
		$implementationCode .= "}\n\n";
		$withImplementationCode .= "}\n\n";

		file_put_contents($this->cacheDirectory."/Mock_".$className.".php",$code.$implementationCode.$withImplementationCode);
	}
}