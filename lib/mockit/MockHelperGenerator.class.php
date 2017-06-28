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

		$classLevelMethodDefinitions = array();
		$class = $reflectionClass;
		do
		{
			if (preg_match('/\@method\s+([\S]+)\s+(\S+)/',$class->getDocComment(),$matches))
			{
				$methodName = $matches[2];
				if(!isset($classLevelMethodDefinitions[$methodName]))
				{
					$classLevelMethodDefinitions[$methodName] = '* @method Mock_'.$matches[1]." ".$matches[2]."\n";
				}
			}
			$class = $class->getParentClass();
		}
		while(!is_null($class) && $class !== false);


		$withImplementationCode = "/**\n".implode("\n",$classLevelMethodDefinitions)." */\n";
		$withImplementationCode .= 'interface Mock_with_implementation_'.$className." \n{\n";
		foreach($reflectionClass->getMethods() as $method) /* @var $method ReflectionMethod */
		{
			if($method->isConstructor() || $method->isStatic())
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

			$returnType = MockitGenerator::getReturnTypeFor($reflectionClass,$method->getName());
			if(
				!is_null($returnType)
				&& !in_array(strtolower($returnType), array('void','mixed','string','int','array','bool','uuid','varint','integer','longtext','longblob','boolean','tinyint','text','float'))
				&& (!preg_match('/^char([^\w]|$)/',$returnType))
				&& (!preg_match('/^varchar([^\w]|$)/',$returnType))
				&& (!preg_match('/^decimal([^\w]|$)/',$returnType))
				&& (!preg_match('/^enum([^\w]|$)/',$returnType))
				&& (!preg_match('/^set([^\w]|$)/',$returnType))
				&& (!preg_match('/^int([^\w]|$)/',$returnType))
				&& (strpos($returnType,'[]') === false)
				&& (class_exists($returnType) || interface_exists($returnType)))
			{
				$withImplementationCode .= "\t/**\n\t *  @return Mock_".$returnType."\n\t */\n\tfunction ".$method->getName()."(".implode(',',$parameters).");\n\n";
			}

			$implementationCode .= "\t/**\n\t * @return MockitStub\n\t*/\n\tfunction ".$method->getName()."(".implode(',',$parameters).");\n\n";
		}

		$implementationCode .= "}\n\n";
		$withImplementationCode .= "}\n\n";

		file_put_contents($this->cacheDirectory."/Mock_".$className.".php",$code.$implementationCode.$withImplementationCode);
	}
}