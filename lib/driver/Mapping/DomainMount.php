<?php
namespace frdl\mount\driver\Mapping;


use steevanb\PhpTypedArray\{
    Exception\InvalidTypeException,
    ScalarArray\StringArray,
    ObjectArray\CodePointStringArray
};

use Symfony\Component\String\CodePointString;

final class DomainMount extends CodePointStringArray
{
	   
    public function __construct(string $host = '', string $location = '')
    {
        parent::__construct([$host, $location], CodePointString::class);
	if(2!==$this->count()){
	  throw new \Exception('Invalid array count (key,value=2 required) in '.__METHOID__);	
	}
    }
  
    public function toArray(): array
    {
        return [$this->values[0] => $this->values[1]];
    }
   	
    public function __get($name)
    {
        if($name === 'host' || 'key' === $name){
		return $this->values[0];
	}elseif($name === 'location' || 'value' === $name){
		return $this->values[1];
	}
    }
	
}

