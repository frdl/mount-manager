<?php

use Psr\Container\ContainerInterface;


use function DI\factory;
use function DI\decorate;
use function DI\get;
use function DI\add;

use frdl\mount\Manager;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
use bovigo\vfs\vfsStreamWrapper;


return [
 'extensions' => add([
       'mountmanager' => [
             'key' => 'mounting',
             'name' => 'MountManager',
             'manager' => Manager::class,
       ],
 ]),
 'services' => add([
 
 ]),
 'managers' => add([
        'mounting' => [
             'class' => Manager::class,
        ],
 
 ]),
 'drivers' => [
 
 ], 
 'mounts' => [
 
 ],

 'protocols' => [
 
 ],



 'domains' => [
 
 ],



 'stages' => [
      'init',
      'workspace',	 
      'dev',	 
      'green',
      'blue',
      'proxy',	 
 ],


];


		  
		  
	 
