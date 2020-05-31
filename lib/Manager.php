<?php
namespace frdl\mount;

use DeGraciaMathieu\Manager\Manager as AbstractManager;

class Manager extends AbstractManager
{
	
    /**
     * @var boolean
     */
    protected $singleton = false;

    /**
     * @var \DeGraciaMathieu\Manager\Aggregator
     */
    protected $aggregator;
	
	
	protected static $_id = 0;
	protected static $wrapper = 'magic';
	protected static $started = false;
	protected static $mounts = [
  
        ];

	protected static $drivers = [
  
        ];

	protected $_driver;
	public $id;
	public $context;
	
	protected $scheme = null;
        
	protected $mountName;
        protected static $instance = null;
	
	public static function getInstance($scheme=null, $mount=null){
	   	if(null===self::$instance){
		   self::$instance = new self($scheme, $mount);	
		}		
		
	  if (!is_null($scheme) && !is_null($mount)){
		self::$instance->driver = self::driver_object($scheme, $mount);
	  }
		
	   return self::$instance;	
	}
	
	
	public function __construct($scheme=null, $mount=null){
		
		parent::__construct();
		
		self::init();
		
		if (self::$_id >= \PHP_INT_MAX)
			self::$_id = 0;
		
		$this->id = self::$_id++;
		

		
		
		if (!is_null($scheme))
			$this->scheme =$scheme;
		
		if (!is_null($mount))
			$this->mountName =$mount;
		
		if (is_null($this->scheme))
			$this->scheme = self::$wrapper;
			
		if (is_null($this->context))
			$this->context = stream_context_create(['magic'=>['id'=>$this->id]]);
		else
			stream_context_set_option($this->context,['magic'=>['id'=>$this->id]]);
		
			
		if(is_null($this->mountName)){
			$this->mountName = $this->getDefaultDriver().'_disk_'.$this->id;
		}		
		
		
		if (!is_null($this->scheme) && !is_null($this->mountName)){		
			$this->driver = self::driver_object($this->scheme, $this->mountName);	
		}
	
	}
	
	
    /**
     * Get a driver instance.
     *
     * @param  string  $name
     * @return mixed
     *
     * @throws \DeGraciaMathieu\Manager\Exceptions\DriverOverwrittenException
     * @throws \DeGraciaMathieu\Manager\Exceptions\DriverResolutionException
     */
    public function iDriver($type = null)
    {
        $type = $type ?: self::getInstance()->getDefaultDriver();

        $driver = $this->load($type);

        return $driver;
    }		
	
	
    public function __get($name)
    {
	if(isset($this->{'_'.$name})){
	    return $this->{'_'.$name};	
	}elseif(isset($this->{$name})){
	    return $this->{$name};	
	}
    }
	
    public function __set($name, $value)
    {
	if(isset($this->{'_'.$name})){
	     $this->{'_'.$name} = $value;	
	}elseif(isset($this->{$name}) && !is_array($this->{$name})){
	        $this->{$name} = $value;		
	}
    }
	
    public static function __callStatic($method, $parameters)
    {
	if('driver'===$method){
	    return self::registerDriver(...$parameters);
	}elseif(\preg_match("/^create([A-Z][.*]+)Driver$/", $method, $matches)    ){
	  $type = \strtolower($matches[1]);
	  \array_unshift($parameters, $type);	
	  return self::getInstance($type)->makeDriver(...$parameters);
	}
	    
     //   return self::getInstance()->driver()->$method(...$parameters);
	return call_user_func_array([self::getInstance(), $method], $parameters);    
    }
	
    public function __call($method, $parameters)
    {
	if('driver'===$method){
	    return $this->iDriver(...$parameters);
	}elseif(\preg_match("/^create([A-Z][.*]+)Driver$/", $method, $matches)    ){
	  $type = \strtolower($matches[1]);
	  \array_unshift($parameters, $type);	
	  if(1>=count($parameters))\array_unshift($parameters, []);
	  if(2>=count($parameters))\array_unshift($parameters, $this->mountName);	
	  if(3>=count($parameters))\array_unshift($parameters, $this->scheme);	
	  return $this->makeDriver(...$parameters);
	}
	    
        return $this->driver()->$method(...$parameters);
    }
	
    public function makeDriver($type){
	$parameters = func_get_args();
	array_shift($parameters);
        $class = isset(self::$drivers[$type]) ? self::$drivers[$type] : __NAMESPACE__.'\\driver\\'.\ucfirst($type);    
	$options = (count($parameters)) ? array_shift($parameters) : [];
	$name = (count($parameters)) ? array_shift($parameters) : $this->mountName;
	$scheme = (count($parameters)) ? array_shift($parameters) : $this->scheme;
	
	    
	    
	
		
		if ((!isset(self::$mounts[$scheme]) || !isset(self::$mounts[$scheme][$name]) )  && class_exists($class)){
			if (is_subclass_of($class, __NAMESPACE__.'\\Driver'))
				{
				
				   try{
					if(true!== ($validation=self::validateOptions($class, $options))   ){
					   throw new Exception((string)$validation);	
					}
				   }catch(\Exception $e){		
					   throw new Exception("Could not mount '".$scheme.'://'.$name."': ".$e->getMessage(),102);		 
					   return null;						   
				   }
				
				
						if(!isset(self::$mounts[$scheme])){			  
							self::$mounts[$scheme] = [];			
						}
		
				
				 self::$mounts[$scheme][$name] = new $class($options);
				// if (!self::$mounts[$name]->success())
				// 	throw new Exception("Could not mount '".$name."'.",102);
				//return true;
				//  return self::driver_object($scheme, $name);
				}
			}
	    
	    
	 return self::driver_object($scheme, $name);  
    }
	
    public function getDefaultDriver(){
	return 'fs';
    }
   /**
     * Make a new driver instance.
     *
     * @param  string  $name
     * @return mixed
     *
     * @throws \DeGraciaMathieu\Manager\Exceptions\DriverResolutionException
     */
    public function makeDriverInstance(string $type)
    {
	self::init();    
	    
        $method = 'create' . ucfirst(strtolower($type)) . 'Driver';

      //  if (! method_exists($this, $method)) {
      //      throw new DriverResolutionException('Driver [' . $name . '] not supported.');
    //    }

        return $this->$method($type);
    }
	
	
    public static function init(){		
		if (!self::$started){
			self::$started = true;			
			
			spl_autoload_register(self::class.'::autoload');			
			//stream_wrapper_register(self::$wrapper,self::class,\STREAM_IS_URL);	
			self::alias(self::$wrapper,\STREAM_IS_URL);	
			
			\class_alias(self::class, \MagicMounter\Magic::class);
		}
		
    }
    
    
	/**
	 * Fetches a driver object, if it exists.
	 * @internal
	 * @param string $name
	 * @return MagicMounter\Driver|false
	 */
	protected static function driver_object($scheme, $name){
		$name = \strtolower($name);
		return isset(self::$mounts[$scheme]) && isset(self::$mounts[$scheme][$name]) ? self::$mounts[$scheme][$name] : false;		
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
		self::init();
			
		$name = \strtolower($name);
		
		
		if (!preg_match('/^[a-z0-9._-]+$/',$name))
			throw new Exception("Invalid mount name '[".$scheme.'://]'.$name."'.",104);
		
				
		
		
		if (isset(self::$mounts[$scheme]) && isset(self::$mounts[$scheme][$name]))
			throw new Exception("Mount point '".$scheme.'://'.$name."' already exists.",101);
		
		
		  if(!in_array($scheme, \stream_get_wrappers())) {  
			self::alias($scheme);
		  } 
		

			
		try{				
			self::getInstance($scheme, $name)			
				->{'create'.\ucfirst($type).'Driver'}($options, $name, $scheme)		
			;				 
		}catch(\Exception $e){					
			throw new Exception("Could not mount '".$scheme.'://'.$name."': ".$e->getMessage(),102);			
			return false;					
		}	
		
		return self::mounted($scheme, $name);
		
		/*
		$class = isset(self::$drivers[$type]) ? self::$drivers[$type] : __NAMESPACE__.'\\driver\\'.\ucfirst($type);
		
		if (class_exists($class)){
			if (is_subclass_of($class, __NAMESPACE__.'\\Driver'))
				{
				
				   try{
					if(true!== ($validation=self::validateOptions($class, $options))   ){
					   throw new Exception((string)$validation);	
					}
				   }catch(\Exception $e){		
					   throw new Exception("Could not mount '".$scheme.'://'.$name."': ".$e->getMessage(),102);		 
					   return false;						   
				   }
				
				
						if(!isset(self::$mounts[$scheme])){			  
							self::$mounts[$scheme] = [];			
						}
		
				
				self::$mounts[$scheme][$name] = new $class($options);
				// if (!self::$mounts[$name]->success())
				// 	throw new Exception("Could not mount '".$name."'.",102);
				return true;
				}
			}
		
	
		throw new Exception("Could not mount '".$scheme.'://'.$name."', the driver does not exist or is invalid.",102);
		 return false;	
		 */
	}

	public static function validateOptions($class, array &$options){
		$eFn = function($o, $opt){
		    return 'You MUST provide a valid configuration option for the'.((true===$o['required'])?' required':'').' `'.$o['key'].'`-Field. ('.$o['hint'].')';
		};
		
		$opt = call_user_func($class.'::getOptions');
		foreach($opt as $o){
		    if(isset($o['required']) && true=== $o['required'] && !isset($options[$o['key']])){
	        	 return $eFn($o, $options[$o['key']]);
		    }elseif(isset($o['default']) && (!isset($o['required']) || true!== $o['required']) && !isset($options[$o['key']])){
			$options[$o['key']] = $o['default'];    
		    }
			
	            if(true=== $o['required'] && 
		      (
		       (is_string($o['type']) && gettype($options[$o['key']]) !== $o['type']) 
			||       
			(is_callable($o['type']) && true !== call_user_func($o['type'],$options[$o['key']])  )    
		      )
		      ){
			  return $eFn($o, $options[$o['key']]);
		    }
		}
		
		
		return true;
	}
	

	
	
	/**
	 * Checks whether a magic mount exists.
	 * @param string $name
	 * @return bool
	 */
	public static function mounted($scheme, $name){
		return isset(self::$mounts[$scheme]) && isset(self::$mounts[$scheme][\strtolower($name)]);		
	}

	/**
	 * Unmounts a magic mount.
	 * @param string $name
	 * @return bool
	 */
	public static function unmount($scheme, $name){
		if ($driver = self::driver_object($scheme, $name))
			{
			unset(self::$mounts[$scheme][strtolower($name)]);
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
	public static function mode($mode,$value = null){
		self::init();
		
		switch ($mode)
			{
			case 'url_stream':
				$url_stream = !stream_is_local(self::$wrapper.'://');
				if ($value === null)
					return $url_stream;
				if ($url_stream == $value)
					return true;
				//@stream_wrapper_unregister(self::$wrapper);
				//return stream_wrapper_register(self::$wrapper,'MagicMounter\\Magic',($value?STREAM_IS_URL:0));
                                  return self::alias(self::$wrapper,($value?STREAM_IS_URL:0));
			case 'wrapper':
				if ($value === null)		
					return self::$wrapper;
				if (!preg_match('/^[a-z0-9.]+$/',$value))
					throw new Exception('Illegal wrapper name, only a-z, 0-9, and dots are allowed.',2);
				$url_stream = !stream_is_local(self::$wrapper.'://');
				//@stream_wrapper_unregister(self::$wrapper);
				self::$wrapper = $value;
				//return stream_wrapper_register(self::$wrapper,'MagicMounter\\Magic',($url_stream?STREAM_IS_URL:0));
				 return self::alias(self::$wrapper,($url_stream?STREAM_IS_URL:0));
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
	public static function registerDriver($type,Driver $driver = null){
		self::init();
		
		
		if (\func_num_args() === 1)
			{
			$class = isset(self::$drivers[$type]) ? self::$drivers[$type] : __NAMESPACE__.'\\driver\\'.\ucfirst($type);
			return class_exists($class) ? $class : null;
			}
		if (!is_subclass_of($driver,__NAMESPACE__.'\\Driver'))
			throw new Exception("Driver '".$name."', should implement interface \\frdl\\mount\\Driver.",4);
		
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
	public static function quote($scheme, $magic_stream/*, ...*/){
		self::init();
		$parameters = \func_get_args();
		array_shift($parameters);
		array_shift($parameters);
		if (is_string($magic_stream)){
			if ($driver = self::driver_object($scheme, strtolower($magic_stream))){
				return $driver->quote($parameters,null);
			}
			throw new Exception("Unknown mount '".$magic_stream."'.",100);			
		}
		
		$meta = \stream_get_meta_data($magic_stream);
		if ($meta['wrapper_type'] === 'user-space' && $meta['wrapper_data'] instanceof self){
			return $meta['wrapper_data']->driver_quote($parameters);
		}
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
	public static function alias($alias,$flags = null,bool $Throw = false){
				
		if (true===$Throw && in_array($alias, \stream_get_wrappers())) {  
			throw new Exception("Protocol '".$alias."' is registered already.",1);
		}elseif (false===$Throw && in_array($alias, \stream_get_wrappers())) {  
			stream_wrapper_unregister($alias);
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
		$class = \strtolower($class);
		
		if (\strpos($class,__NAMESPACE__.'\\driver\\',0) === 0){
			$path = __DIR__.\DIRECTORY_SEPARATOR.'driver'.\DIRECTORY_SEPARATOR.\basename($class).'.php';
			if (!file_exists($path))
				throw new Exception("Specified driver class '".$class."' does not exist.",1);
			
			require $path;			
		}elseif(\strpos($class,'\\MagicMounter\\driver\\',0) === 0){
			$path = __DIR__.\DIRECTORY_SEPARATOR.'..'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'driver'.\DIRECTORY_SEPARATOR.\basename($class).'.php';
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
		$path_info = \parse_url($path);
		$this->scheme = $path_info['scheme'];
		$this->mountName = $path_info['host'];
		
		if ($this->driver = self::driver_object($this->scheme, $this->mountName)){
			return $this->driver->stream_open($path_info,$mode,$options,$opened_path,$this);
		}
		
		  throw new Exception("Unknown mount '".$path_info['host']."'.",100);		
	}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_read($count){
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
		$this->scheme = $path_info['scheme'];
		$this->mountName = $path_info['host'];
		
		if ($this->driver = self::driver_object($this->scheme, $this->mountName)){
			return $this->driver->unlink($path_info,$this);
		}
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function url_stat($path,$flags)
		{
		$path_info = parse_url($path);
		$this->scheme = $path_info['scheme'];
		$this->mountName = $path_info['host'];
		
		if ($this->driver = self::driver_object($this->scheme, $this->mountName)){
			return $this->driver->url_stat($path_info,$flags,$this);
		}
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function stream_metadata($path,$option,$value)
		{
		$path_info = parse_url($path);
		$this->scheme = $path_info['scheme'];
		$this->mountName = $path_info['host'];
		
		if ($this->driver = self::driver_object($this->scheme, $this->mountName)){
			return $this->driver->stream_metadata($path_info,$option,$value,$this);
		}
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function mkdir($path,$mode,$options)
		{
		$path_info = parse_url($path);
		$this->scheme = $path_info['scheme'];
		$this->mountName = $path_info['host'];
		
		if ($this->driver = self::driver_object($this->scheme, $this->mountName)){
			return $this->driver->mkdir($path_info,$mode,$options,$this);
		}
		throw new Exception("Unknown mount '".$path_info['host']."'.",100);
		}

	/**
	 * StreamWrapper internal function
	 * @internal
	 */
	public function rmdir($path,$options)
		{
		$path_info = parse_url($path);
		$this->scheme = $path_info['scheme'];
		$this->mountName = $path_info['host'];
		
		if ($this->driver = self::driver_object($this->scheme, $this->mountName)){
			return $this->driver->rmdir($path_info,$options,$this);
		}
		
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
		
		$this->driver = self::driver_object($path_info['scheme'], $path_info['host']);
		
		
		if ($this->driver)
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
		$this->scheme = $path_info['scheme'];
		$this->mountName = $path_info['host'];
		
		if ($this->driver = self::driver_object($this->scheme, $this->mountName)){
			return $this->driver->dir_opendir($path_info,$options,$this);
		}
		
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
