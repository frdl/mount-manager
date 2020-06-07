<?php

namespace frdl\mount\driver;
/*
 * (c) Andrey F. Mindubaev <covex.mobile@gmail.com>

           - (c) edited by frdlweb

*/
use Covex\Stream;


use Covex\Stream\FileSystem;
use Covex\Stream\Changes;




use Covex\Stream\File\Entity;
use Covex\Stream\File\EntityInterface;

use Covex\Stream\Partition;

use frdl\mount\Manager;

use frdl\mount\DriverInterface;
use frdl\mount\Exception;



class Transactional extends \frdl\mount\driver\Delegate
{
    /**
     * @var Partition[]
     */
    protected static $partitions = [];

    /**
     * @var array
     */
    protected $dirFiles;

    /**
     * @var resource
     */
    protected $filePointer;

    /**
     * @var EntityInterface
     */
    protected $fileEntity;
    protected $options;

    public function __construct(array $opts = [])
    {
        $this->filePointer = null;
        $this->fileEntity = null;
               
         $this->options=array_merge([
                        'scheme' => 'frdl',
                        'directory' => null,
			   
                    ], $opts);        
               
               
	
		 $this->options['target'] = new FileSystem;
		
		
		
               if(count($this->options)>0){
                     call_user_func_array([$this, 'register'], [
                                   $this->options['scheme'],
                                   $this->options['directory']
                                ]);        
               }
			  
    }

	
	
public static function getOptions() :array{
	  return [
         [	  
	  'key' => 'scheme', 		  
		'required' => true,  
                'default' => 'frdl',
		'type' => (function(string $i){		
		    return is_string($i);	
		}),
		'hint' => 'Mounted stage.',    
	      ],  
    
     
  	  
      [	  
	  'key' => 'directory', 		  
		'required' => false,  
                'default' => null,
		'type' => (function(string $i = null){
                     return is_null($i) || (is_string($i) && is_dir($i)  );	
		}),
		'hint' => 'Mounted stage.',   
	      ],  
    
    
	  
	  ];
     }           
  
 
    public function register(string $protocol, string $root = null, int $flags = 0)
    {
		
		$class = get_class($this->options['target']);
		$class::register( $protocol,  $root,  $flags);
		
		/*
        $wrappers = stream_get_wrappers();
        if (in_array($protocol, $wrappers) && isset(self::$partitions[$protocol]) && self::$partitions[$protocol] instanceof Partition) {
           stream_wrapper_unregister($protocol); 
			Manager::unmount($scheme, $name);
        }
		
	 
		$wrappers = stream_get_wrappers();
        if (in_array($protocol, $wrappers)) {
            throw new Exception(
                "Protocol '$protocol' has been registered already"
            );
        }	
	
		
		
		
		
        $wrapper = stream_wrapper_register($protocol, get_class($this->options['target']), $flags);

        if ($wrapper) {
            if (null !== $root) {
                $content = Entity::newInstance($root);
            } else {
                $content = null;
            }

            $partition = new Partition($content);

            self::$partitions[$protocol] = $partition;
        }

        return $wrapper;
		*/
    }


    public static function commit(string $protocol): bool
    {
        if (isset(self::$partitions[$protocol])) {
            self::$partitions[$protocol]->commit();

            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }


    public static function unregister(string $protocol): bool
    {
        unset(self::$partitions[$protocol]);

        $wrappers = stream_get_wrappers();
        if (!in_array($protocol, $wrappers)) {
            throw new Exception(
                "Protocol '$protocol' has not been registered yet"
            );
        }

        return stream_wrapper_unregister($protocol);
    }


    public static function getRelativePath(string $url): string
    {
        $urlParts = explode('://', $url);
        array_shift($urlParts);
        $urlPath = implode('://', $urlParts);

        return Entity::fixPath($urlPath);
    }


    public static function getPartition(string $url): ?Partition
    {
        $urlParts = explode('://', $url);
        $protocol = array_shift($urlParts);

        return self::$partitions[$protocol] ?? null;
    }
  /*       */   
           
           
          
  /*           
  
    public function url_stat(array  $path_info,  $flags,Manager $magic_stream = null)
    {
		$url = Manager::unparse_url($path_info);
        $partition = static::getPartition($url);
        $path = static::getRelativePath($url);

        return $partition->getStat($path, $flags);
    }

	
	

    public function stream_open(array $url,$mode,$options,&$opened_path,Manager $magic_stream)
    {
		//$url = Manager::unparse_url($path_info);
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        $this->filePointer = $partition->fileOpen(
            $path, $mode, $options
        );

        $result = (bool) $this->filePointer;
        if ($result && ($options & STREAM_USE_PATH)) {
            $openedPath = $path;
        }

        return $result;
    }
	

    public function mkdir(array  $path_info, int $mode, int $options,Manager $magic_stream = null)
    {
		$url = Manager::unparse_url($path_info);
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        return (bool) $partition->makeDirectory($path, $mode, $options);
    }

  
    public function rmdir(array  $path_info, int $options,Manager $magic_stream = null)
    {
		$url = Manager::unparse_url($path_info);
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        return (bool) $partition->removeDirectory($path, $options);
    }

 
    public function unlink(array  $path_info,Manager $magic_stream = null)
    {
		$url = Manager::unparse_url($path_info);
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        return (bool) $partition->deleteFile($path);
    }

   
    public function rename(array  $path_info, string $dstPath,Manager $magic_stream = null)
    {
		$srcPath = Manager::unparse_url($path_info);
        $partition = self::getPartition($srcPath);

        $srcRelativePath = self::getRelativePath($srcPath);
        $dstRelativePath = self::getRelativePath($dstPath);

        return (bool) $partition->rename($srcRelativePath, $dstRelativePath);
    }

  
    public function dir_opendir(array  $path_info,Manager $magic_stream = null)
    {
		$url = Manager::unparse_url($path_info);
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        $files = $partition->getList($path);
        if (is_array($files)) {
            $this->dirFiles = [];
            foreach ($files as $file) {
                $this->dirFiles[] = $file->basename();
            }
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }


    public function dir_readdir(Manager $magic_stream = null)
    {
        $value = current($this->dirFiles);

        if (false === $value) {
            $result = false;
        } else {
            $result = $value;
            next($this->dirFiles);
        }

        return $result;
    }


    public function dir_closedir(Manager $magic_stream = null)
    {
        unset($this->dirFiles);

        return true;
    }

   
    public function dir_rewinddir(Manager $magic_stream = null)
    {
        reset($this->dirFiles);

        return true;
    }



    public function stream_close(Manager $magic_stream = null)
    {
        fclose($this->filePointer);
    }

 
    public function stream_read( $count,Manager $magic_stream = null)
    {
        return fread($this->filePointer, $count);
    }

 
    public function stream_stat(Manager $magic_stream = null)
    {
        return fstat($this->filePointer);
    }

  
    public function stream_eof(Manager $magic_stream = null)
    {
        return feof($this->filePointer);
    }

    
    public function stream_tell(Manager $magic_stream = null)
    {
        return ftell($this->filePointer);
    }

   
    public function stream_seek( $offset,  $whence = SEEK_SET,Manager $magic_stream = null)
    {
        return 0 === fseek($this->filePointer, $offset, $whence);
    }


    public function stream_write( $data,Manager $magic_stream = null)
    {
        return fwrite($this->filePointer, $data);
    }

   
    public function stream_flush(Manager $magic_stream = null)
    {
        return fflush($this->filePointer);
    }


   public function stream_metadata(array $path_info,$option,$value,Manager $magic_stream)
    {
	   $url = Manager::unparse_url($path_info);
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        switch ($option) {
            case STREAM_META_TOUCH:
                $result = $partition->touch($path, $value[1] ?? null, $value[2] ?? null) ? true : false;
                break;
            default:
                $result = false;
        }

        return $result;
    }

   */
}
