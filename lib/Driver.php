<?php
namespace frdl\mount;

use frdl\mount\Manager;
use frdl\mount\DriverInterface;


abstract class Driver implements DriverInterface
	{
	
	 protected $shouldBeSingleton = false;
	
	 public function singleton(bool $is = null){
		 if(\is_bool($is)){
		    $this->shouldBeSingleton=$is;	 
		 }
		 
		 
	     return $this->shouldBeSingleton;	 
	 }
	
	abstract public function __construct(array $options);
	abstract public static function getOptions() :array;
	
	abstract public function quote(array $parameters,Manager $magic_stream = null);

	// stream wrapper functions
	abstract public function stream_open(array $path_info,$mode,$options,&$opened_path,Manager $magic_stream);
	abstract public function stream_read($count,Manager $magic_stream);
	abstract public function stream_eof(Manager $magic_stream);
	abstract public function stream_stat(Manager $magic_stream);
	abstract public function stream_seek($offset,$whence,Manager $magic_stream);
	abstract public function stream_tell(Manager $magic_stream);
	abstract public function stream_truncate($new_size,Manager $magic_stream);
	abstract public function stream_write($data,Manager $magic_stream);
	abstract public function stream_set_option($option,$arg1,$arg2,Manager $magic_stream);
	abstract public function stream_lock($operation,Manager $magic_stream);
	abstract public function stream_flush(Manager $magic_stream);
	abstract public function stream_cast($cast_as,Manager $magic_stream);
	abstract public function stream_close(Manager $magic_stream);

	abstract public function unlink(array $path_info,Manager $magic_stream);
	abstract public function url_stat(array $path_info,$flags,Manager $magic_stream);
	abstract public function stream_metadata(array $path_info,$option,$value,Manager $magic_stream);

	abstract public function mkdir(array $path_info,$mode,$options,Manager $magic_stream);
	abstract public function rmdir(array $path_info,$options,Manager $magic_stream);
	abstract public function rename(array $path_info_from,array $path_info_to,Manager $magic_stream);

	abstract public function dir_opendir(array $path_info,$options,Manager $magic_stream);
	abstract public function dir_closedir(Manager $magic_stream);
	abstract public function dir_readdir(Manager $magic_stream);
	abstract public function dir_rewinddir(Manager $magic_stream);
}
