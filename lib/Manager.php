<?php
namespace frdl\mount;

class Manager
{
	protected static $_id = 0;
	protected static $wrapper = 'magic';
	protected static $mounts = [
  
        ];

	protected static $drivers = [
  
        ];

	protected $driver;
	public $id;
	public $context;
	
	protected $scheme;
  

	public function __construct()
		{
		if (self::$_id >= \PHP_INT_MAX)
			self::$_id = 0;
		
		$this->id = self::$_id++;
		if (is_null($this->context))
			$this->context = stream_context_create(['magic'=>['id'=>$this->id]]);
		else
			stream_context_set_option($this->context,['magic'=>['id'=>$this->id]]);
		}
    
    
    public static function init()
		{
		static $started = false;
		if (!$started){
			$started = true;
			
			
			spl_autoload_register(self::class.'::autoload');			
			//stream_wrapper_register(self::$wrapper,self::class,\STREAM_IS_URL);	
			self::alias(self::$wrapper,\STREAM_IS_URL);
			
		}
		
    }
    
    
	/**
	 * Fetches a driver object, if it exists.
	 * @internal
	 * @param string $name
	 * @return MagicMounter\Driver|false
	 */
	protected static function driver_object($name)
		{
		$name = strtolower($name);
		return isset(self::$mounts[$name]) ? self::$mounts[$name] : false;
		}



	/**
	 * Create a new magic mount.
	 * @param string $name Name of the mount.
	 * @param string $type Type of the mount (which driver to load).
	 * @param array $options Driver-specific options to pass.
	 * @return bool
	 */
	public static function mount($scheme, $name,$type,$options = [])
		{
				
		  if (!in_array($scheme, \stream_get_wrappers())) {  
			self::alias($scheme);
		  } 
		
		
		$name = strtolower($name);
		if (isset(self::$mounts[$scheme][$name]))
			throw new Exception("Mount point '".$name."' already exists.",101);
		if (!preg_match('/^[a-z0-9._-]+$/',$name))
			throw new Exception("Invalid mount name '".$name."'.",104);
		$class = isset(self::$drivers[$type]) ? self::$drivers[$type] : __NAMESPACE__.'\\driver\\'.$type;
		if (class_exists($class))
			{
			if (is_subclass_of($class, __NAMESPACE__.'\\Driver'))
				{
				self::$mounts[$scheme][$name] = new $class($options);
				// if (!self::$mounts[$name]->success())
				// 	throw new Exception("Could not mount '".$name."'.",102);
				return true;
				}
			}
		throw new Exception("Could not mount '".$scheme.'://'.$name."', the driver does not exist or is invalid.",102);
		// return false;
		}

	/**
	 * Checks whether a magic mount exists.
	 * @param string $name
	 * @return bool
	 */
	public static function mounted($scheme, $name)
		{
		return isset(self::$mounts[strtolower($name)]);
		}

	/**
	 * Unmounts a magic mount.
	 * @param string $name
	 * @return bool
	 */
	public static function unmount($name)
		{
		if ($driver = self::driver_object($name))
			{
			unset(self::$mounts[strtolower($name)]);
			return $driver->unmount();
			}
		return false; //TODO- throw exception
		}

	/**
	 * Set or get a MagicMounter mode.
	 * @param string $mode
	 * @param mixed $value
	 * @return mixed
	 */
	public static function mode($mode,$value = null)
		{
		switch ($mode)
			{
			case 'url_stream':
				$url_stream = !stream_is_local(self::$wrapper.'://');
				if ($value === null)
					return $url_stream;
				if ($url_stream == $value)
					return true;
				@stream_wrapper_unregister(self::$wrapper);
				return stream_wrapper_register(self::$wrapper,'MagicMounter\\Magic',($value?STREAM_IS_URL:0));

			case 'wrapper':
				if ($value === null)		
					return self::$wrapper;
				if (!preg_match('/^[a-z0-9.]+$/',$value))
					throw new Exception('Illegal wrapper name, only a-z, 0-9, and dots are allowed.',2);
				$url_stream = !stream_is_local(self::$wrapper.'://');
				@stream_wrapper_unregister(self::$wrapper);
				self::$wrapper = $value;
				return stream_wrapper_register(self::$wrapper,'MagicMounter\\Magic',($url_stream?STREAM_IS_URL:0));
			}
		return false;
		}

	/**
	 * Sets or gets a driver class name for a specific magic mount type. Call with one argument to
	 * get the driver class name for the passed type, call with two arguments to set. You can use
	 * this to overwrite default drivers as well.
	 * @param string $type
	 * @param string|null $driver The fully-qualified class name or null to reset to default.
	 * @return string|void
	 */
	public static function driver($type,$driver = null)
		{
		if (func_num_args() === 1)
			{
			$class = isset(self::$drivers[$type]) ? self::$drivers[$type] : '\\MagicMounter\\driver\\'.$type;
			return class_exists($class) ? $class : null;
			}
		if (!is_subclass_of($driver,'\\MagicMounter\\Driver'))
			throw new Exception("Driver '".$name."', should implement interface \\MagicMounter\\Driver.",4);
		if (self::$drivers[$type] === null)
			unset(self::$drivers[$type]);
		else
			self::$drivers[$type] = $driver;
		}

	/**
	 * Call a driver-specific method on a magic driver or magic stream. Parameters are dynamic.
	 * @param string|resource $magic_stream Mount name or magic stream resource.
	 * @return mixed
	 */
	public static function quote($magic_stream/*, ...*/)
		{
		$parameters = func_get_args();
		array_shift($parameters);
		if (is_string($magic_stream))
			{
			if ($driver = self::driver_object(strtolower($magic_stream)))
				return $driver->quote($parameters,null);
			throw new Exception("Unknown mount '".$magic_stream."'.",100);
			}
		$meta = stream_get_meta_data($magic_stream);
		if ($meta['wrapper_type'] === 'user-space' && $meta['wrapper_data'] instanceof self)
			return $meta['wrapper_data']->driver_quote($parameters);
		throw new Exception('Passed stream is not a Magic stream.',3);
		}

	/**
	 * Passes the quote() call onto the driver.
	 * @internal
	 * @return mixed
	 */
	public function driver_quote($parameters)
		{
		return $this->driver->quote($parameters,$this);
		}

	/**
	 * Register an alias stream wrapper for use with MagicMounter. This method merely interfaces stream_wrapper_register()
	 * @param string $alias
	 * @param int $flags 0 or STREAM_IS_URL. Default: STREAM_IS_URL
	 * @return bool
	 */
	public static function alias($alias,$flags = null){
		
		if (in_array($alias, \stream_get_wrappers())) {  
			throw new Exception("Protocol '".$alias."' is registered already.",1);
		} 
		
		
		if ($flags === null)
			$flags = \STREAM_IS_URL;
		
		   return stream_wrapper_register($alias, self::class,$flags);
		}

	/**
	 * MagicMounter driver autoloader. Should not be called directly.
	 * @internal
	 * @param string $class
	 * @return void
	 */
	public static function autoload($class)
		{
		$class = strtolower($class);
		if (strpos($class,'magicmounter\\driver\\',0) === 0)
			{
			$path = __DIR__.'/driver/'.substr($class,20).'.php';
			if (!file_exists($path))
				throw new Exception("Specified driver class '".$class."' does not exist.",1);
			require $path;
			}
		}



	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_open($path,$mode,$options,&$opened_path)
		{
		$path_info = parse_url($path);
		if ($this->driver = self::driver_object($path_info['host']))
			return $this->driver->stream_open($path_info,$mode,$options,$opened_path,$this);
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_read($count)
		{
		return $this->driver->stream_read($count,$this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_eof()
		{
		return $this->driver->stream_eof($this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_stat()
		{
		return $this->driver->stream_stat($this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_seek($offset,$whence = SEEK_SET)
		{
		return $this->driver->stream_seek($offset,$whence,$this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_tell()
		{
		return $this->driver->stream_tell($this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_truncate($new_size)
		{
		return $this->driver->stream_truncate($new_size,$this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_write($data)
		{
		return $this->driver->stream_write($data,$this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_set_option($option,$arg1,$arg2)
		{
		return $this->driver->stream_set_option($option,$arg1,$arg2,$this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_lock($operation)
		{
		return $this->driver->stream_lock($operation);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_flush()
		{
		return $this->driver->stream_flush($this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_cast($cast_as)
		{
		return $this->driver->stream_cast($cast_as,$this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_close()
		{
		return $this->driver->stream_close($this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function unlink($path)
		{
		$path_info = parse_url($path);
		if ($this->driver = self::driver_object($path_info['host']))
			return $this->driver->unlink($path_info,$this);
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function url_stat($path,$flags)
		{
		$path_info = parse_url($path);
		if ($this->driver = self::driver_object($path_info['host']))
			return $this->driver->url_stat($path_info,$flags,$this);
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_metadata($path,$option,$value)
		{
		$path_info = parse_url($path);
		if ($this->driver = self::driver_object($path_info['host']))
			return $this->driver->stream_metadata($path_info,$option,$value,$this);
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function mkdir($path,$mode,$options)
		{
		$path_info = parse_url($path);
		if ($this->driver = self::driver_object($path_info['host']))
			return $this->driver->mkdir($path_info,$mode,$options,$this);
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function rmdir($path,$options)
		{
		$path_info = parse_url($path);
		if ($this->driver = self::driver_object($path_info['host']))
			return $this->driver->rmdir($path_info,$options,$this);
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function rename($path_from,$path_to)
		{
		$path_info = parse_url($path_from);
		$path_info_to = parse_url($path_to);
		if ($path_info['host'] !== $path_info_to['host'])
			throw new Exception('Cannot rename a file across magic mounts.',110);
		if ($this->driver = self::driver_object($path_info['host']))
			return $this->driver->rename($path_info,$path_info_to,$this);
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function dir_opendir($path,$options)
		{
		$path_info = parse_url($path);
		if ($this->driver = self::driver_object($path_info['host']))
			return $this->driver->dir_opendir($path_info,$options,$this);
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function dir_closedir()
		{
		return $this->driver->dir_closedir($this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function dir_readdir()
		{
		return $this->driver->dir_readdir($this);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function dir_rewinddir()
		{
		return $this->driver->dir_rewinddir($this);
		}
	}
