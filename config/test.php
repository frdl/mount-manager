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
	Manager::class => function(ContainerInterface $c){
                 $Manager = Manager::getInstance('web+fan', 'test');
		return $Manager;
	},	
	
	
'config.managers.mounting' => add([		
         [
              'name' => 'vhost.test',
              'scheme' =>  'web+vfs',
              'type' => 'virtual',
              ''options' => [
                   'root' => '~',
                    'fs.virtual.structure' => [
                    
                    ],
                    'directory' => null,
                    
                    'target' => new vfsStreamWrapper
              ],
	      
	      
             [
              'name' => 'project.workspace.frdl',
              'scheme' =>  'frdl',
              'type' => 'transactional',
              ''options' => [
                    'scheme'=> 'project',
                    'directory' => get('root.dir'),
              ],     
	]),

];


		  
		  
	 
      
