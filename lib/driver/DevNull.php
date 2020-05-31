<?php

namespace frdl\mount\driver;

use frdl\mount\Manager;
use frdl\mount\Exception;
use frdl\mount\Driver;

use frdl\ContextContainer;

class DevNull implements Driver
{
   protected $options;	
	
   public function __construct($options){
  	$this->options=(!is_object($options) || true!==$options instanceof ContextContainer)
			? ContextContainer::create($options, '${', '}')
			: $options	
			;
  }
  
	public static function getOptions() :array{
    return [];
  }
	
	public function quote(array $parameters,Manager $magic_stream = null){
     return null;
  }

	// stream wrapper functions
	public function stream_open(array $path_info,$mode,$options,&$opened_path,Manager $magic_stream){
    return false;
  }
	public function stream_read($count,Manager $magic_stream){
   return null;
  }
	public function stream_eof(Manager $magic_stream){
   return true;
  }
	public function stream_stat(Manager $magic_stream){
   return false;
  }
	public function stream_seek($offset,$whence,Manager $magic_stream){
   return false;
  }
	public function stream_tell(Manager $magic_stream){
    return false;
  }
	public function stream_truncate($new_size,Manager $magic_stream){
    return false;
  }
	public function stream_write($data,Manager $magic_stream){
   return false;
  }
	public function stream_set_option($option,$arg1,$arg2,Manager $magic_stream){
   return false;
  }
	public function stream_lock($operation,Manager $magic_stream){
   return false;
  }
	public function stream_flush(Manager $magic_stream){
   return false;
  }
	public function stream_cast($cast_as,Manager $magic_stream){
   return false;
  }
	public function stream_close(Manager $magic_stream){
   return false;
  }

	public function unlink(array $path_info,Manager $magic_stream){
   return false;
  }
	public function url_stat(array $path_info,$flags,Manager $magic_stream){
   return [];
  }
	public function stream_metadata(array $path_info,$option,$value,Manager $magic_stream){
   return [];
  }

	public function mkdir(array $path_info,$mode,$options,Manager $magic_stream){
   return false;
  }
	public function rmdir(array $path_info,$options,Manager $magic_stream){
   return false;
  }
	public function rename(array $path_info_from,array $path_info_to,Manager $magic_stream){
   return false;
  }

	public function dir_opendir(array $path_info,$options,Manager $magic_stream){
   return false;
  }
	public function dir_closedir(Manager $magic_stream){
   return false;
  }
	public function dir_readdir(Manager $magic_stream){
   return false;
  }
	public function dir_rewinddir(Manager $magic_stream){
   return false;
  }
	
}
