<?php

namespace frdl\mount\driver;

use frdl\mount\Manager;
use frdl\mount\Exception;
use frdl\mount\Driver;

use frdl\ContextContainer;

class Delegate implements Driver
{
   protected $options;	
   
	
  public function __construct($options){     

		$this->options=(!is_object($options) || true!==$options instanceof ContextContainer)
			? ContextContainer::create($options, '${', '}')
			: $options	
			;
 
 }


	
	public static function getOptions() :array{
	  return [
    
  	  
      [	  
	  'key' => 'target', 		  
		'required' => false,  
    'default' => null,
		'type' => function(&$i){
		     
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
    $method = 'parent::' . $method;
    if (is_callable($method)) {
      return call_user_func_array($method, $arguments);
    } else {
      return FALSE;
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
     return null;
  }


	
  
}
