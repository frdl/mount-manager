<?php

namespace frdl\mount\driver;

use frdl\mount\Manager;
use frdl\mount\Exception;
use frdl\mount\Driver;
use frdl\mount\driver\Delegate;

use VirtualFileSystem\FileSystem;

use Nijens\ProtocolStream\StreamManager;

use frdl\ContextContainer;

class Mock extends Delegate
{

  protected $singleton = true;
  protected $options = [];


	
	protected $fs;

 
	
 
	public function __construct($options){     
	
		$this->options=(!is_object($options) || true!==$options instanceof ContextContainer)
			? ContextContainer::create($options, '${', '}')
			: $options	
			;
		
		$this->init();
	}

	
	public function init(){
	
		 $this->fs = new FileSystem();
	   $this->fs->container->root()->setScheme(this->options->get('scheme'));
     
	  return $this;	
	}
	
	public static function getOptions() :array{
	  return 
    [
    
    
	      [	  
	        'key' => 'scheme', 		  
		'required' => true,  
    'default' => [],
		'type' => function(string $i){
		      return \is_string($i);
		},
		'hint' => 'The scheme of the virtual filesystem.',     
	      ],
        
        

	  ];
	}
	
	

}
