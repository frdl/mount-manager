<?php

namespace frdl\mount\driver;

use frdl\mount\Manager;
use frdl\mount\Exception;
use frdl\mount\Driver;
use frdl\mount\driver\Delegate;

use frdl\ContextContainer;
use Nijens\ProtocolStream\StreamManager;

use frdl\mount\DriverInterface;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\Quota;


class Virtual extends Delegate
{
    protected $options;	
    protected static $StreamManager = null;
    protected static $MountManager = null;
    protected $vfsStreamDirectory = null;
	
	protected $shouldBeSingleton = true;
	
  public function __construct($options){   
  	  
	  $this->options = $options;
	   $this->options['target'] = new vfsStreamWrapper;
	

	  $Wrapper = $this->options['target']; 
	  $Wrapper::register();  	  
	  
     parent::__construct($this->options);

			  

	$this->rootDirectory(vfsStream::setup(
          $this->options['root'],
          $this->options['permissions'],
        $this->options['fs.virtual.structure']
    )); 

   
	  	  
	 if(isset($this->options['quota']) && null !== $this->options['quota']){
		vfsStreamWrapper::setQuota(new Quota($this->options['quota'])); 
	 }

 }
	
 protected function rootDirectory(vfsStreamDirectory $dir = null) :vfsStreamDirectory 
 {
	 if(!is_null($dir)){
	    $this->vfsStreamDirectory = $dir;	 
	 }
	 
   return $this->vfsStreamDirectory;	 
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
	
	public static function getOptions() :array{
	  return [
		  
	      [	  
	  'key' => 'root', 		  
		'required' => false,  
                'default' => 'home',
		'type' => function(string $i = null){
                     return \is_null($i) || \is_string($i) ;	
		},
		'hint' => 'Root: The Virtual FS HOME Directory.',     
	      ],  
    
		      [	  
	     'key' => 'quota', 		  
		'required' => false,  
         'default' => null,
		'type' => function(int $i = null){
               return \is_null($i) || \is_int($i) ;	
		},
		'hint' => 'Root: The Virtual FS Quota.',     
	      ],  
    
		  
		  
		      [	  
	  'key' => 'permissions', 		  
		'required' => false,  
              'default' => null,
		'type' => function( $i = null){
                     return \is_null($i) || \is_int($i) ;	
		},
		'hint' => 'Root: The Virtual FS Permissions.',     
	      ],  	  
	   
		  
       [	  
	  'key' => 'directory', 		  
		'required' => false,  
                'default' => null,
		'type' => function(string $i = null){
                     return \is_null($i) || (\is_string($i) && \is_dir($i)  );	
		},
		'hint' => 'Real Physical Filesystem Directory Mounting (optional).',     
	      ],  
    
       
      [	  
	  'key' => 'fs.virtual.structure', 		  
		'required' => false,  
                'default' => [],
		'type' => function(array $i = null){
                     return \is_array($i);	
		},
		'hint' => 'Abstract Filesystem Structure and Contents AsArray (optional).',     
	      ], 
		  	/*  
      [	  
	  'key' => 'target', 		  
		'required' => false,  
                'default' =>  vfsStreamWrapper::class,
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
    
    */
	  
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
