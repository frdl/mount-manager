<?php

namespace frdl\mount;

use frdl\mount\DriverInterface;

class Repository
{

    const METHOD_MAP = [
        'singleton',
        'quote',
        'stream_open',
        'stream_eof',
        'stream_stat',
        'stream_seek',
        'stream_tell',
        'stream_truncate',
        'stream_write',
        'stream_set_option',
        'stream_lock',
        'stream_flush',
        'stream_cast',
        'stream_close',
        'unlink',
        'url_stat',
        'stream_metadata',
        'mkdir',
        'rmdir',
        'rename',
        'dir_opendir',
        'dir_closedir',
        'dir_readdir',
        'dir_rewinddir',
                      
    ];
    const METHOD_MAP_STATIC = [
        'getOptions'
    ];
    
    protected $driver;
    protected static $classNames = [];
    
    public function __construct(DriverInterface $driver,string $type = null)
    {
        $this->driver = $driver;
        if(\is_string($type) && !isset(self::$classNames[$type])){
            self::$classNames[$type] = get_class($this->driver);
        }
    }
    
    public static function call($method, $arguments, string $type = null){
       
        if(\is_string($type) ){
              $class = self::$classNames[$type];     
        }else{  
               $class = Driver::class;          
         }
        
           return call_user_func_array($class.'::'.$method, $arguments); 
    }
    
    public function __call($method, $arguments){
        if(\in_array($method, self::METHOD_MAP)){
           return call_user_func_array([$this->driver, $method], $arguments);   
        }
    }
    
    

}
