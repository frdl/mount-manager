<?php
namespace frdl\mount\driver\Mapping;

use frdl\mount\driver\Mapping\DNS;

final class StreamMapping extends \IteratorIterator
{
   protected $protocol;
   protected $writable;	
   protected $map;	
	
   public function __construct(string $protocol, bool $writable, DNS $mountDNS/*\Generator $map = null*/){
	   $this->protocol = $protocol;
	   $this->writable = $writable;
	   $this->map = $this->createMapping($mountDNS);
   }
	
	
  public function createMapping(DomainMount... $entries){
	    while ($mount = array_shift($entries)) {  
	        yield $mount; 
	    }
  }
	
}
