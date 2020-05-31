<?php

namespace frdl\mount\driver;

use frdl\mount\Manager;
use frdl\mount\Exception;
use frdl\mount\Driver;
use frdl\mount\driver\DevNull;
use frdl\mount\driver\Fs;
use frdl\mount\driver\Delegate;

use frdl\mount\driver\Mapping\DomainMount;
use frdl\mount\driver\Mapping\DNS;
use frdl\mount\driver\Mapping\StreamMapping;

use Nijens\ProtocolStream\StreamManager;

use frdl\ContextContainer;

class Mapping extends Delegate
{

  protected $singleton = true;
  protected $options = [];
  protected static $StreamManager = null;
  protected $initiated = false;	
	
	

 
	
 
	public function __construct($options){     
	    if(null === self::$StreamManager){	
	        self::$StreamManager = StreamManager::create();		
	    }
		$this->options=(!is_object($options) || true!==$options instanceof ContextContainer)
			? ContextContainer::create($options, '${', '}')
			: $options	
			;
		
		
		$this->options['mappings']->add($this->options['protocol-domain-mappings']);
		
	 
		
		$this->init();
	}

	
	public function init(){
		if(true===$this->initiated){
		  return;	
		}
		$this->initiated=true;
		
		$mappings = [];
		foreach($this->options['mappings'] as $scheme => $map){	
		    	$mountDNS=[];
			foreach($map['DNS'] as $domain => $path){			
				$mountDNS[]=new DomainMount($domain, $path);		
			}
			
		  $DNS = new DNS($mountDNS);
		  $mappings[]= new StreamMapping($scheme,
					        $map['writable'], 
						$DNS, 
					       function($value, $protocol, $Writable){
						       
                                                   $stream = new Stream($protocol, 
									$value,
									$Writable
								       );

                                                    StreamManager::create()->registerStream($stream);
						   foreach($value as $host => $location){    
						      Manager::mount('frdlweb:'.$stream->getProtocol(), $host,'delegate',
								     ['target'=>$stream->getProtocol().'://'.$host,
								     'mappings'=>array_merge(
									 $stream->getPaths(),
								      [
								         $host => $location
								      ]]
											    )
								      );
						   }
					       }
		   );	
		}
		$this->options->set('${self}.config.map', $mappings);
		
	  return $this;	
	}
	
	public static function getOptions() :array{
	  return array_merge(parent::getOptions(), [
    
    
	      [	  
	        'key' => 'protocol-domain-mappings', 		  
		'required' => false,  
    'default' => [],
		'type' => function(array $i){
		      return \is_array($i);
		},
		'hint' => 'Mapping of an ArrayOf([scheme://][domain]) to local locations or delegating streams.';     
	      ],
        
        
	      [	  
	        'key' => 'mappings', 		  
		'required' => false,  
    'default' => [],
		'type' => function(array $i){
		      return \is_array($i);
		},
		'hint' => 'Mapping of an ArrayOf([scheme://][domain]) to local locations or delegating streams.';     
	      ],        
	  ]);
	}
	
	

}
