<?php

namespace frdl\mount\driver;

use frdl\mount\Manager;
use frdl\mount\Exception;
use frdl\mount\Driver;

use frdl\ContextContainer;

/**
 * MagicMounter, by Marvin Janssen (http://marvinjanssen.me), released in 2017.
 * 
 * This is a local filesystem magic driver, it is mainly meant as an example even though it can
 * be used to simulate symbolic links.
 */

use frdl\mount\DriverInterface;

class Fs extends Driver implements DriverInterface
	{
	protected $options=[];
	protected $directory;
	protected $resources = [];
	protected $directories = [];

	public function __construct($options)
		{
		
		
		$this->options=(!is_object($options) || true!==$options instanceof ContextContainer)
			? ContextContainer::create($options, '${', '}')
			: $options	
			;
		
		if (!isset($this->options['directory']))
			throw new Exception("The 'directory' option is required.",200);
		if (empty($this->options['directory']))
			throw new Exception("The 'directory' option cannot be empty.",201);
		$this->directory = $this->options['directory'];
		if (!is_dir($this->directory))
			throw new Exception("Could not mount '".$this->directory."', the directory does not exist.",202);
	}

	public static function getOptions() :array{
	  return [
	      [	  
	        'key' => 'directory', 		  
		'required' => true,      
		'type' => function(string $dir){
		     if(empty($dir) || !\is_dir($dir) || (!\is_readable($dir) && !\is_writable($dir)) ){
			return false;     
		     }else{
			 return true;    
		     }
		},
		'hint' => 'A (relative or absolute) path to a local directory.',     
	      ],
	  ];
	}
	
	
	public function __destruct()
		{
		$this->unmount();
		}

	public function unmount()
		{
		foreach ($this->resources as $resource)
			{
			if (is_resource($resource))
				@fclose($resource);
			}
		foreach ($this->directories as $resource)
			{
			if (is_resource($resource))
				@closedir($resource);
			}
		$this->resources = [];
		$this->directories = [];
		return true;
		}

	public function quote(array $parameters,Manager $magic_stream = null)
		{
		return null;
		}

	public function stream_open(array $path_info,$mode,$options,&$opened_path,Manager $magic_stream)
		{
		$opened_path = $this->directory.(isset($path_info['path']) ? $path_info['path'] : '');
		if ($mode & STREAM_REPORT_ERRORS)
			$this->resources[$magic_stream->id] = fopen($opened_path,$mode,false,$magic_stream->context);
		else
			$this->resources[$magic_stream->id] = @fopen($opened_path,$mode,false,$magic_stream->context);
		return $this->resources[$magic_stream->id] !== false;
		}

	public function stream_read($count,Manager $magic_stream)
		{
		return fread($this->resources[$magic_stream->id],$count);
		}

	public function stream_eof(Manager $magic_stream)
		{
		return feof($this->resources[$magic_stream->id]);
		}

	public function stream_stat(Manager $magic_stream)
		{
		return fstat($this->resources[$magic_stream->id]);
		}

	public function stream_seek($offset,$whence,Manager $magic_stream)
		{
		return fseek($this->resources[$magic_stream->id],$offset,$whence);
		}

	public function stream_tell(Manager $magic_stream)
		{
		return ftell($this->resources[$magic_stream->id]);
		}

	public function stream_truncate($new_size,Manager $magic_stream)
		{
		return ftruncate($this->resources[$magic_stream->id],$new_size);
		}

	public function stream_write($data,Manager $magic_stream)
		{
		return fwrite($this->resources[$magic_stream->id],$data);
		}

	public function stream_set_option($option,$arg1,$arg2,Manager $magic_stream)
		{
		switch ($option)
			{
			case STREAM_OPTION_BLOCKING:
				return stream_set_blocking($this->resources[$magic_stream->id],$arg1);
			case STREAM_OPTION_READ_TIMEOUT:
				return stream_set_timeout($this->resources[$magic_stream->id],$arg1,$arg2);
			case STREAM_OPTION_WRITE_BUFFER:
				return stream_set_write_buffer($this->resources[$magic_stream->id],$arg1) === 0;
			}
		return false;
		}

	public function stream_lock($operation,Manager $magic_stream)
		{
		return flock($this->resources[$magic_stream->id],$operation);
		}

	public function stream_flush(Manager $magic_stream)
		{
		return fflush($this->resources[$magic_stream->id]);
		}

	public function stream_cast($cast_as,Manager $magic_stream)
		{
		return isset($this->resources[$magic_stream->id]) ? $this->resources[$magic_stream->id] : false;
		}

	public function unlink(array $path_info,Manager $magic_stream)
		{
		return @unlink($this->directory.(isset($path_info['path']) ? $path_info['path'] : ''));
		}

	public function url_stat(array $path_info,$flags,Manager $magic_stream)
		{
		//copy() does a url_stat before copying
		if ($flags & STREAM_URL_STAT_QUIET)
			return @stat($this->directory.(isset($path_info['path']) ? $path_info['path'] : ''));
		return stat($this->directory.(isset($path_info['path']) ? $path_info['path'] : ''));
		}

	public function stream_metadata(array $path_info,$option,$value,Manager $magic_stream)
		{
		switch ($option)
			{
			case STREAM_META_TOUCH:
				return touch($this->directory.(isset($path_info['path']) ? $path_info['path'] : ''),$value[0],$value[1]);
			case STREAM_META_OWNER_NAME:
			case STREAM_META_OWNER:
				return chown($this->directory.(isset($path_info['path']) ? $path_info['path'] : ''),$value);
			case STREAM_META_GROUP_NAME:
			case STREAM_META_GROUP:
				return chgrp($this->directory.(isset($path_info['path']) ? $path_info['path'] : ''),$value);
			case STREAM_META_ACCESS:
				return chmod($this->directory.(isset($path_info['path']) ? $path_info['path'] : ''),$value);
			}
		return false;
		}

	public function stream_close(Manager $magic_stream)
		{
		$result = fclose($this->resources[$magic_stream->id]);
		unset($this->resources[$magic_stream->id]);
		return $result;
		}

	public function mkdir(array $path_info,$mode,$options,Manager $magic_stream)
		{
		return mkdir($this->directory.(isset($path_info['path']) ? $path_info['path'] : ''),$mode,(bool)($options & STREAM_MKDIR_RECURSIVE),$magic_stream->context);
		}

	public function rmdir(array $path_info,$options,Manager $magic_stream)
		{
		return rmdir($this->directory.(isset($path_info['path']) ? $path_info['path'] : ''));
		}

	public function rename(array $path_info_from,array $path_info_to,Manager $magic_stream)
		{
		return rename($this->directory.(isset($path_info_from['path']) ? $path_info_from['path'] : ''),$this->directory.(isset($path_info_to['path']) ? $path_info_to['path'] : ''),$magic_stream->context);
		}

	public function dir_opendir(array $path_info,$options,Manager $magic_stream)
		{
		$opened_path = $this->directory.(isset($path_info['path']) ? $path_info['path'] : '');
		$this->directories[$magic_stream->id] = opendir($opened_path,$magic_stream->context);
		return $this->directories[$magic_stream->id] !== false;
		}

	public function dir_closedir(Manager $magic_stream)
		{
		$result = closedir($this->directories[$magic_stream->id]);
		unset($this->directories[$magic_stream->id]);
		return $result;
		}

	public function dir_readdir(Manager $magic_stream)
		{
		return readdir($this->directories[$magic_stream->id]);
		}

	public function dir_rewinddir(Manager $magic_stream)
		{
		rewinddir($this->directories[$magic_stream->id]);
		return true;
		}
	}
