<?php

class AutoLoader
{
	private $allClassfiles;
	private $directories;
	private $generatedDir;

	/**
	 * @param string[] $directories
	 */
	function __construct(array $directories, $generatedDir=null)
	{
		$this->directories = $directories;
		$this->generatedDir = $generatedDir;
	}

	public static function autoload(array $directories, $generatedDir)
	{
		$autoLoader = new AutoLoader($directories, $generatedDir);
		spl_autoload_register(array($autoLoader,'autoloadClass'));
		return $autoLoader;
	}

	public function resetClassFiles()
	{
		$this->allClassfiles = null;
		$this->getAllClassfiles();
	}

	/**
	 * @return string[]
	 */
	public function getAllClassfiles()
	{
		if(!isset($this->allClassfiles))
		{
			$cacheClassName = get_class($this).'Cache';
			if(file_exists($this->generatedDir.'/'.$cacheClassName.'.class.php') && !defined('FRAMEWORKIT_GENERATING'))
			{
				require_once($this->generatedDir.'/'.$cacheClassName.'.class.php');
				$cache = new $cacheClassName();
				$this->allClassfiles = $cache->cache;
			}
			else
			{
				$this->allClassfiles = array();
				foreach($this->directories as $directory)
				{
					$this->allClassfiles = array_merge($this->allClassfiles, $this->getClassfilesRecursively($directory));
				}
				if(!is_null($this->generatedDir))
				{
					$this->allClassfiles = array_merge($this->allClassfiles, $this->getClassfilesRecursively($this->generatedDir));
				}
			}
		}

		return $this->allClassfiles;
	}

	public function getGeneratedDir()
	{
		return $this->generatedDir;
	}

	/**
	 * @param string $dir
	 *
	 * @return string[]
	 */
	private function getClassfilesRecursively($dir)
	{
		$result = array();
		$dp = @opendir($dir);
		while(($dp !== false) && (($file = readdir($dp)) !== false))
		{
			if(substr($file, 0, 1) != ".")
			{
				if(is_dir("$dir/$file"))
				{
					$result = array_merge($result, $this->getClassfilesRecursively("$dir/$file"));
				}
				else if(($className = $this->getClassNameForFile("$dir/$file")) !== false)
				{
					$result[$className] = str_replace('//', '/', "$dir/$file");
				}
			}
		}
		return $result;
	}

	public function canLoadClass($className)
	{
		$classes = $this->getAllClassfiles();
		return isset($classes[$className]);
	}

	/**
	 * @param string $filename
	 *
	 * @return string|false
	 */
	public function getClassNameForFile($filename)
	{
		if(!preg_match('/([A-Za-z][A-Za-z0-9_]+)\.class\.php$/', $filename, $matches))
		{
			return false;
		}

		return $matches[1];
	}

	private static $i = 0;

	/**
	 * @param string $className
	 *
	 * @return void
	 */
	public function autoloadClass($className)
	{
		$classes = $this->getAllClassfiles();

		if(isset($classes[$className]))
		{
			require_once $classes[$className];
		}
		else
		{
			$this->onClassNotFound($className);
		}
	}

	/**
	 * @param string $className
	 *
	 * @return void
	 */
	protected function onClassNotFound($className)
	{
		if(substr($className,0,6) == 'DbAuto')
		{
			$baseClass = substr($className,6);
			try
			{
				eval('class DbAuto' . $baseClass . ' extends ' . $baseClass . ' {}');
			}
			catch (ParseError $parseError)
			{
				throw new RuntimeException("Parse error executing eval() function:\n" . $parseError->getMessage(), 1717, $parseError);
			}
			return;
		}
		if(count(spl_autoload_functions()) == 1)
		{
			$ex = new Exception("Could not load ".$className/*." from ".print_r($this->getAllClassfiles(), true)*/); // I commented out the print_r because it was way too big to get any good info out of
			if(strpos($ex->getTraceAsString(),'class_exists') === false)
			{
				throw $ex;
			}
		}
		else
		{
			// Allow other auto-loaders to do their magic.
		}
	}
}
