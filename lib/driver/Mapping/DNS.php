<?php
namespace frdl\mount\driver\Mapping;

use frdl\mount\driver\Mapping\DomainMount;
use steevanb\PhpTypedArray\ObjectArray\ObjectArray;


final class DNS extends ObjectArray
{
	   
   public function __construct($entries){
       parent::__construct($entries, DomainMount::class);
   }
  
}
