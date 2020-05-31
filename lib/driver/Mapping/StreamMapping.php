<?php
namespace frdl\mount\driver\Mapping;

use frdl\mount\driver\Mapping\DNS;

final class StreamMapping extends \ArrayIterator
{
	
   protected $callback=null;
   protected $protocol;
   protected $writable;	
   protected $values = [];	
	
   public function __construct(string $protocol, bool $writable, DNS $mountDNS, callable $callback = null){
	   $this->callback=$callback;
	   $this->protocol = $protocol;
	   $this->writable = $writable;

	 foreach( $this->createMapping($this->protocol, $this->writable, $mountDNS) as $mount){
		array_push($this->values, [$mount->key => $mount->value]); 
	 }
   }
	
	
  public function createMapping(string $protocol,bool $writable=true, DomainMount... $entries){
	    while ($mount = array_shift($entries)) {  
	        yield $mount; 
	    }
  }
	
  public function current() :DomainMount {
    $value = parent::current();
    if(!is_callable($this->callback)){
	return $value;    
    }
    return call_user_func($this->callback, [$value, $this->protocol, $this->writable]);
  } 	
	

   
public function offsetGet($offset) :DomainMount
    {
        return parent::offsetGet($offset);
    }
	
}
