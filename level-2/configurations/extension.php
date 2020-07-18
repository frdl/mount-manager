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

 'managers' => add([
    'mounting' => [
        [
              'name' => 'v.test',
              'scheme' =>  'web+vfs',
              'type' => 'virtual',
              'options' => [
                   'scheme' => 'virtual',
                    
              ],	
	   ],	      
            
	[
              'name' => 'project',
              'scheme' =>  'web+project',
              'type' => 'transactional',
               'options' => [
                    'scheme'=> 'project',
                    'directory' => get('root.dir'),
              ],    		    
	   
	],

   ],
 
 ]),


];
