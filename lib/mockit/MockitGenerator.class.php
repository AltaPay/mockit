<?php

class MockitGenerator
{
	public static function getReturnTypeFor(ReflectionClass $clazz, $methodName, $isDynamic=true)
	{
		$returnClass = null;
		if($clazz->hasMethod($methodName) && preg_match('/\@return\s+([^\s]+)/',$clazz->getMethod($methodName)->getDocComment(),$matches))
		{
			$returnClass = $matches[1];
		}
		else if($clazz->hasMethod($methodName) || $isDynamic)
		{
			$class = $clazz;
			do
			{
				if (preg_match('/\@method\s+([^\s]+)\s+'.$methodName.'\s/',$class->getDocComment(),$matches))
				{
					$returnClass = $matches[1];
					break;
				}
				$class = $class->getParentClass();
			}
			while(!is_null($class) && $class !== false);
		}
		return $returnClass;
	}

	public static function getMockitor(ReflectionClass $class, Mockit $mock)
	{
		$mockitorClassname = 'Mockitor_'.$class->name;

		if(!class_exists($mockitorClassname, false))
		{
			if($class->isInterface())
			{
				$tmpl = 'class '.$mockitorClassname.' implements '.$class->name.'{';
			}
			else
			{
				$tmpl = 'class Mockitor_'.$class->name.' extends '.$class->name.'{';
			}
			$tmpl .= "\n";
			$tmpl .= 'private $mock;'."\n";
			$tmpl .= 'public function __construct(Mockit $mock) { $this->mock = $mock; }'."\n";
			foreach($class->getMethods(ReflectionMethod::IS_PUBLIC|ReflectionMethod::IS_PROTECTED) as $method) /* @var $method ReflectionMethod */
			{
				if($method->name == '__construct' || $method->isStatic() || $method->isFinal())
				{
					continue;
				}

				$tmpl .= self::getMethodSignature($class, $method);
				$tmpl .= '{'."\n";
				if(count(self::getClasslessArgs($class, $method)) == 0)
				{
					$tmpl .= "if(count(func_get_args()) > 0){ throw new Exception('Method ".$class->getName()."->".$method->getName()."() was called with arguments even though it takes none'); }\n";
				}
				$tmpl .= "\t".'return $this->mock->process(new MockitEvent($this->mock, "'.$method->getName().'", array('.implode(',',self::getClasslessArgs($class, $method)).'),$this->mock->getEvents()->count()));'."\n";
				$tmpl .= '}'."\n";
			}
			$tmpl .= '}';

			eval($tmpl);
		}
		return new $mockitorClassname($mock);
	}

	public static function getSpy(ReflectionClass $class, $original, Mockit $mock)
	{
		$mockitorClassname = 'Spyor_'.$class->name;

		if(!class_exists($mockitorClassname, false))
		{
			$tmpl = 'class '.$mockitorClassname.' extends '.$class->name.'{';

			$tmpl .= "\n";
			$tmpl .= 'private $mock;'."\n";
			$tmpl .= 'public function __construct(Mockit $mock, $original) {'."\n";
			$tmpl .= '$this->mock = $mock;'."\n";
			foreach($class->getProperties() as  $property) /* @var $property ReflectionProperty */
			{
				if(($property->isPublic() || $property->isProtected()) && !$property->isStatic())
				{
					$tmpl .= '$this->'.$property->getName().' = $original->'.$property->getName().';'."\n";
				}
			}
			$tmpl .= '}'."\n";
			foreach($class->getMethods() as $method) /* @var $method ReflectionMethod */
			{
				if($method->name == '__construct' || $method->isStatic() || $method->isFinal())
				{
					continue;
				}

				$tmpl .= self::getMethodSignature($class, $method);
				$tmpl .= '{'."\n";
				if(count(self::getClasslessArgs($class, $method)) == 0)
				{
					$tmpl .= "\tif(count(func_get_args()) > 0){ throw new Exception('Method ".$class->getName()."->".$method->getName()."() was called with arguments even though it takes none'); }\n";
				}
				if($method->name == '__call')
				{
					$tmpl .= "\t".'if($this->mock->isDynamic()) {'."\n";

					$tmpl .= "\t\t".'$args = func_get_args();'."\n";
					$tmpl .= "\t\t".'$event = new MockitEvent($this->mock, $args[0], $args[1],$this->mock->getEvents()->count());'."\n";
					$tmpl .= "\t\t".'$mockProcessEventResult = $this->mock->process($event);'."\n";
					$tmpl .= "\t\t".'if($this->mock->haveStubEvent($event)) { return $mockProcessEventResult; } else { return parent::'.$method->getName().'('.implode(',',self::getClasslessArgs($class, $method)).'); } '."\n";
					$tmpl .= "\t".'}'."\n";
					$tmpl .= "\t".'else'."\n";
				}
				$tmpl .= "\t".'{'."\n";
				$tmpl .= "\t\t".'$event = new MockitEvent($this->mock, "'.$method->getName().'", array('.implode(',',self::getClasslessArgs($class, $method)).'),$this->mock->getEvents()->count());'."\n";
				$tmpl .= "\t\t".'$mockProcessEventResult = $this->mock->process($event);'."\n";
				$tmpl .= "\t\t".'if($this->mock->haveStubEvent($event)) { return $mockProcessEventResult; } else { return parent::'.$method->getName().'('.implode(',',self::getClasslessArgs($class, $method)).'); } '."\n";
				$tmpl .= "\t".'}'."\n";

				$tmpl .= '}'."\n";

			}
			$tmpl .= '}';

			eval($tmpl);
		}
		return new $mockitorClassname($mock, $original);
	}

	/**
	 * There is a duplicate of this method in AopWrapperGenerator
	 */
	private static function getMethodSignature(ReflectionClass $class, ReflectionMethod $method)
	{
		$tmpl = "\t#[\ReturnTypeWillChange]\n\t".($method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private')).' function '.$method->name.'(';
		$args = array();
		$classlessArgs = array();
		foreach($method->getParameters() as $parameter) /* @var $parameter ReflectionParameter */
		{
			$paramString = '';

			if(!is_null($parameter->getType()))
			{
                if($parameter->getType() instanceof ReflectionNamedType) {
                    $paramString .= $parameter->getType()->getName() . ' ';
                }
			}
			if($parameter->isPassedByReference())
			{
				$paramString .= '&';
			}
            if ($parameter->isVariadic()) {
                $paramString .= "...";
            }
			$paramString .= '$'.$parameter->getName();

			if($parameter->isDefaultValueAvailable())
			{
				switch(gettype($parameter->getDefaultValue()))
				{
					case 'boolean':
						$paramString .= ' = '.($parameter->getDefaultValue() ? 'true' : 'false');
						break;
					case 'integer':
					case 'double':
						$paramString .= ' = '.$parameter->getDefaultValue();
						break;
					case 'string':
						$paramString .= " = '".$parameter->getDefaultValue()."'";
						break;
					case 'NULL':
						$paramString .= ' = null';
						break;
					case 'array':
						$paramString .= ' = array()';
						break;
					case 'object':
						throw new Exception('default value with object. what to do!?');
					case 'resource':
						throw new Exception('default value with resource. what to do!?');

				}

			}
			else if(($parameter->allowsNull() || $parameter->isOptional()) && !$parameter->isVariadic())
			{

				$paramString .= ' = null';
			}
			$args[] = $paramString;
			$classlessArgs[] = '$'.$parameter->getName();
		}
		$tmpl .= implode(',',$args);

		$tmpl .= ')'.(!is_null($method->getReturnType()) ? ' : '.$method->getReturnType()->getName() : '')."\n";

		return $tmpl;
	}

	private static function getClasslessArgs(ReflectionClass $class, ReflectionMethod $method)
	{
		$classlessArgs = array();
		foreach($method->getParameters() as $parameter) /* @var $parameter ReflectionParameter */
		{
			$classlessArgs[] = '$'.$parameter->getName();
		}
		return $classlessArgs;
	}

}