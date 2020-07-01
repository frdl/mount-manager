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

use Covex\Stream\File\Virtual;
use Covex\Stream\File\VirtualInterface;


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
               
               
	
		 $this->options['target'] = new TransactionalFileSystem;
		
		
	
		
		
              
                     call_user_func_array([$this, 'register'], [
                                   $this->options['scheme'],
                                   $this->options['directory']
                                ]);        
             
			  //	 parent::__construct($this->options);
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
    
    
      [	  
	  'key' => 'target', 		  
		'required' => false,  
                'default' =>  TransactionalFileSystem::class,
		'type' => function(&$i){
     //    if('vfs://'===$i){
       //     return true;
        // }
    
    
		     if(\is_string($i)){
		       	$i = Manager::getInstance()->getMountsByPath($i);
		     }
			
		    if(\is_array($i) && !isset($i['driver']) ){
		      	  $i = array_shift($i);    
		    }
			
			 
		    if(\is_array($i) && isset($i['driver']) ){
			        $i = $i['driver'];    
		    }	
			
		    return \is_object($i) && ($i instanceof DriverInterface || \is_callable([$i, 'stream_open']));	
		},
		'hint' => 'Delegate this StreamWrapper to another target StreamWrapper.',    
	      ],  	  
	  ];
     }           
  
  public function getTargetStreamWrapper($method, $arguments) {
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
	

	
    public function register(string $protocol, string $root = null, int $flags = 0)
    {
		
		$class = get_class($this->options['target']);
		$class::register( $protocol,  $root,  $flags);

    }

	
	public function quote(array $parameters,Manager $magic_stream = null)
		{
		echo 'print_r($parameters);<pre>';
		 print_r($parameters);
		echo '</pre>';
		return true;
	}
           
  	public function commit(string $url, array $parameters = null)
	{
		
		return TransactionalFileSystem::commit($url);
	}         
   
}




final class TransactionalFileSystem
{
    /**
     * @var Partition[]
     */
    private static $partitions = [];

    /**
     * @var array
     */
    private $dirFiles;

    /**
     * @var resource
     */
    private $filePointer;

    /**
     * @var EntityInterface
     */
    private $fileEntity;

    public function __construct()
    {
        $this->filePointer = null;
        $this->fileEntity = null;
    }

    /**
     * Retrieve information about a file.
     *
     * @return array|bool
     */
    public function url_stat(string $url, int $flags)
    {
        $partition = static::getPartition($url);
        $path = static::getRelativePath($url);

        return $partition->getStat($path, $flags);
    }

    /**
     * Create a directory. This method is called in response to mkdir().
     *
     * @see http://www.php.net/manual/en/streamwrapper.mkdir.php
     */
    public function mkdir(string $url, int $mode, int $options): bool
    {
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        return (bool) $partition->makeDirectory($path, $mode, $options);
    }

    /**
     * Removes a directory. This method is called in response to rmdir().
     *
     * @see http://www.php.net/manual/en/streamwrapper.rmdir.php
     */
    public function rmdir(string $url, int $options): bool
    {
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        return (bool) $partition->removeDirectory($path, $options);
    }

    /**
     * Delete a file. This method is called in response to unlink().
     *
     * @see http://www.php.net/manual/en/streamwrapper.unlink.php
     */
    public function unlink(string $url): bool
    {
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        return (bool) $partition->deleteFile($path);
    }

    /**
     * Rename a file or directory.
     *
     * @see http://www.php.net/manual/en/streamwrapper.rename.php
     */
    public function rename(string $srcPath, string $dstPath): bool
    {
        $partition = self::getPartition($srcPath);

        $srcRelativePath = self::getRelativePath($srcPath);
        $dstRelativePath = self::getRelativePath($dstPath);

        return (bool) $partition->rename($srcRelativePath, $dstRelativePath);
    }

    /**
     * Open directory handle. This method is called in response to opendir().
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-opendir.php
     */
    public function dir_opendir(string $url): bool
    {
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

    /**
     * Read entry from directory handle. This method is called in response to readdir().
     *
     * @return string|bool
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-readdir.php
     */
    public function dir_readdir()
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

    /**
     * Close directory handle. This method is called in response to closedir().
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-closedir.php
     */
    public function dir_closedir(): bool
    {
        unset($this->dirFiles);

        return true;
    }

    /**
     * Rewind directory handle. This method is called in response to rewinddir().
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-rewinddir.php
     */
    public function dir_rewinddir(): bool
    {
        reset($this->dirFiles);

        return true;
    }

    /**
     * Opens file or URL. This method is called immediately after the wrapper is initialized.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-open.php
     */
    public function stream_open(string $url, string $mode, int $options, ?string &$openedPath): bool
    {
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

    /**
     * Close an resource. This method is called in response to fclose().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-close.php
     */
    public function stream_close(): void
    {
        fclose($this->filePointer);
    }

    /**
     * Read from stream. This method is called in response to fread() and fgets().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-read.php
     */
    public function stream_read(int $count): string
    {
        return fread($this->filePointer, $count);
    }

    /**
     * Retrieve information about a file resource. This method is called in response to fstat().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-stat.php
     */
    public function stream_stat(): array
    {
        return fstat($this->filePointer);
    }

    /**
     * Tests for end-of-file on a file pointer. This method is called in response to feof().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-eof.php
     */
    public function stream_eof(): bool
    {
        return feof($this->filePointer);
    }

    /**
     * Retrieve the current position of a stream. This method is called in response to ftell().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-tell.php
     */
    public function stream_tell(): int
    {
        return ftell($this->filePointer);
    }

    /**
     * Seeks to specific location in a stream. This method is called in response to fseek().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-seek.php
     */
    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        return 0 === fseek($this->filePointer, $offset, $whence);
    }

    /**
     * Write to stream. This method is called in response to fwrite().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-write.php
     */
    public function stream_write(string $data): int
    {
        return fwrite($this->filePointer, $data);
    }

    /**
     * Flushes the output. This method is called in response to fflush().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-flush.php
     */
    public function stream_flush(): bool
    {
        return fflush($this->filePointer);
    }

    /**
     * Change stream metadata.
     *
     * @param mixed $value
     *
     * @see http://php.net/manual/ru/streamwrapper.stream-metadata.php
     */
    public function stream_metadata(string $url, int $option, $value): bool
    {
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

    /**
     * Register stream wrapper.
     
         $real = Entity::newInstance(__FILE__);
        $virtual = Virtual::newInstance($real, '/tmp/qqq');

        $this->assertEquals($real->basename(), $virtual->basename());
        $this->assertEquals('/tmp/qqq', $virtual->path());
        $this->assertEquals($real, $virtual->getRealEntity());
	
     */
	
	
    public static function getSession($scheme){
	 $k = self::class.'@'.$scheme;
	if(isset($_SESSION) && isset($_SESSION[$k]) && is_array($_SESSION[$k]) ){
	    $_SESSION[$k]['lasthit'] = time();       
	}else{
	      
            $tempDir = rtrim(sys_get_temp_dir(), '\\/');

            do {
                $name = $tempDir.'/'.uniqid('vfs', true);
            } while (file_exists($name));	
		
	   $_SESSION[$k] = [
		'lasthit' => = time(),
		'tempPath' =>   $name, 
	   ];
	}
	    
        return $_SESSION[$k];	  
    }
	
	
    public static function register(string $protocol, string $root = null, int $flags = 0): bool
    {
        $wrappers = stream_get_wrappers();
        if (in_array($protocol, $wrappers)) {
            throw new Exception(
                "Protocol '$protocol' has been already registered"
            );
        }
        $wrapper = stream_wrapper_register($protocol, get_called_class(), $flags);

        if ($wrapper) {
		
		$session = self::getSession($protocol);
		
            if (null !== $root) {
                 $content = Entity::newInstance($root, $session['tempPath']);
            } else {
           //     $content = null;
	         $content = Entity::newInstance(new self, $session['tempPath']);
	    }
	
		
            
	  //   $entity = Virtual::newInstance($partition->getRoot(), $partition->getRealEntity()->path(), $partition->basename());	
	    $entity = Virtual::newInstance($content, $session['tempPath']);	
		
	    $partition = new Partition($entity);
         	
            self::$partitions[$protocol] = $partition;
        }

        return $wrapper;
    }

    /**
     * Commit all changes to real FS.
     */
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

    /**
     * Unregister stream wrapper.
     */
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

    /**
     * Get relative path of an url.
     */
    public static function getRelativePath(string $url): string
    {
        $urlParts = explode('://', $url);
        array_shift($urlParts);
        $urlPath = implode('://', $urlParts);

        return Entity::fixPath($urlPath);
    }

    /**
     * Get partition by file url.
     */
    public static function getPartition(string $url): ?Partition
    {
        $urlParts = explode('://', $url);
        $protocol = array_shift($urlParts);

        return self::$partitions[$protocol] ?? null;
    }
}
