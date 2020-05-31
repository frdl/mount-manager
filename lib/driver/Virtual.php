<?php

namespace frdl\mount\driver;

use frdl\mount\Manager;
use frdl\mount\Exception;
use frdl\mount\Driver;
use frdl\mount\driver\Delegate;

use frdl\ContextContainer;
use Nijens\ProtocolStream\StreamManager;

use frdl\mount\DriverInterface;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;

class Virtual extends Delegate
{
    protected $options;	
    protected static $StreamManager = null;
    protected static $MountManager = null;
    protected $vfsStreamDirectory = null;
	
	
  public function __construct($options){     
    parent::__construct($options);
	    
   $this->rootDirectory(vfsStream::setup(
         '~',
         755,
        $this->options['fs.virtual.structure']
    ));
	  
	  
 }
	
 protected function rootDirectory(vfsStreamDirectory $dir = null) :vfsStreamDirectory 
 {
	 if(\!is_null($dir)){
	    $this->vfsStreamDirectory = $dir;	 
	 }
	 
   return $this->vfsStreamDirectory;	 
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
	  'key' => 'directory', 		  
		'required' => false,  
                'default' => null,
		'type' => function(string $i = null){
                     return \is_null($i) || (\is_string($i) && \is_dir($i)  );	
		},
		'hint' => 'Real Physical Filesystem Directory Mounting (optional).';     
	      ],  
    
       
  	  
      [	  
	  'key' => 'target', 		  
		'required' => false,  
                'default' => 'vfs://',
		'type' => function(&$i){
         if('vfs://'===$i){
            return true;
         }
    
    
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


	
  
}
