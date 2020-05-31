<?php
namespace frdl\mount;

use frdl\mount\Manager;
use frdl\mount\MagicStreamWrapperInterface;


interface DriverInterface extends MagicStreamWrapperInterface
{
	public function __construct(array $options);
	public function singleton(bool $is = null) :bool;
	public static function getOptions() :array;
	public function quote(array $parameters,Manager $magic_stream = null);
	
}
