<?php

namespace frdl\mount\driver;

use frdl\mount\Manager;
use frdl\mount\Exception;
use frdl\mount\Driver;

use frdl\ContextContainer;
use Nijens\ProtocolStream\StreamManager;

use frdl\mount\DriverInterface;

class Delegate implements DriverInterface
{
    protected $options;	
    protected static $StreamManager = null;
    protected static $MountManager = null;
	
  public function __construct($options){     
	    if(null === self::$StreamManager){	
	        self::$StreamManager = StreamManager::create();		
	    }
	    if(null === self::$MountManager){	
	        self::$MountManager = Manager::getInstance();		
	    }	  
	  
		$this->options=(!is_object($options) || true!==$options instanceof ContextContainer)
			? ContextContainer::create($options, '${', '}')
			: $options	
			;
 
 }

public function getTargetStreamWrapper($method, $arguments) :\stdclass{
     $magic_stream = array_pop($arguments);	
					
	                         try{
					if(true!== ($validation=Manager::validateOptions(get_class($this), $this->options))   ){
					   throw new Exception((string)$validation);	
					}
				   }catch(\Exception $e){		
					   throw new Exception("Could not getTargetStreamWrapper for method '".get_class($this).'::'.$method."': ".$e->getMessage(),102);		 
					   return null;						   
				   }
	
	return $this->options['target'];
}
	
	public static function getOptions() :array{
	  return [
    
  	  
      [	  
	  'key' => 'target', 		  
		'required' => false,  
                'default' => 'file://localhost',
		'type' => function(&$i){
		     if(\is_string($i)){
			$i = Manager::getInstance()->getMountsByPath($i);
		     }
			
		    if(\is_array($i) && !isset($i['driver']) ){
			$i = array_shift($i);    
		    }
			
			 
		    if(\is_array($i) && isset($i['driver']) ){
			$i = $i['driver'];    
		    }	
			
		    return \is_object($i) && ($i instanceof Driver || \is_callable([$i, 'stream_open']));	
		},
		'hint' => 'Delegate this StreamWrapper to another target StreamWrapper.';     
	      ],  
    
    
	  
	  ];
	}
  
  

  /**
   * Helper method that will delegate method calls to parent
   * if they are supported. Otherwise it will return FALSE.
   *
   * Since declaring the methods in annotation and using the __call()
   * magic method is not working with interfaces we have to process
   * each method manually.
   *
   * @param string $method
   *   The called class method name.
   * @param array $arguments
   *   Arguments provided to the caller.
   *
   * @return bool|mixed
   *   Returns the return value from existing parent method
   *   or FALSE if the method does not exist.
   */
  protected function delegate($method, $arguments) {
      $Handler = [$this->getTargetStreamWrapper($method, $arguments), $method];
    if(is_callable($Handler)) {
       return call_user_func_array($Handler, $arguments);
    }else{
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function dir_closedir(Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function dir_opendir($path, $options, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function dir_readdir( Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function dir_rewinddir( Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function mkdir($path, $mode, $options, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function rename($path_from, $path_to, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function rmdir($path, $options, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_cast($cast_as, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_close(Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_eof( Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_flush(Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_lock($operation, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_metadata($path, $option, $value, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  public function stream_open($path, $mode, $options = NULL, &$opened_path = NULL, Manager $magic_stream) {
    // Only the first two arguments are supported,
    // so there is no need to take care of the reference.
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_read($count, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_seek($offset, $whence = SEEK_SET, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_set_option($option, $arg1, $arg2, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_stat( Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_tell(Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_truncate($new_size, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function stream_write($data, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function unlink($path, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function url_stat($path, $flags, Manager $magic_stream) {
    return $this->delegate(__FUNCTION__, func_get_args());
  }






	
  public function quote(array $parameters,Manager $magic_stream = null){
      return $this->delegate(__FUNCTION__, func_get_args());
  }


	
  
}
