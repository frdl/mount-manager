<?php
namespace frdl\mount\driver\Mapping;

use frdl\mount\driver\Mapping\DomainMount;

final class DNS implements \Iterator extends \IteratorIterator
{
	   
   public function __construct(DomainMount... $entries){
	    parent::__construct(new \ArrayIterator($entries));
   }
	
  public function current() : DomainMount
  {
    return parent::current();
  }
  
}
