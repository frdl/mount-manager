


# Protocol-Handlers / StreamWrappers Collection Test 0

`virtual, transactional, local, mount`  ToDo: remote, clowd, ...


test.php:
````PHP
<?php

//...
use frdl\mount\Manager;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use org\bovigo\vfs\Quota;

use Covex\Stream\Changes;
use Covex\Stream\File\Entity;

use frdl\mount\driver\Mapping\DomainMount;
use frdl\mount\driver\Mapping\DNS;


use Nijens\ProtocolStream\Stream\Stream;
use Nijens\ProtocolStream\StreamManager;

 


$StubRunner = require __DIR__.\DIRECTORY_SEPARATOR.'admin.php';

$StubRunner->autoloading();

/*
Admin Guard
*/
if(!$StubRunner->isRootUser()){ 
 die('Remove/uncomment this line to enable public test: '.basename(__FILE__).':'.__LINE__);return; 
}

echo 'test...';

$projectDir =  __DIR__ 
	. \DIRECTORY_SEPARATOR 
	. '..'
	. \DIRECTORY_SEPARATOR
	. '..'	
	. \DIRECTORY_SEPARATOR 
	. 'frdlweb'
	. \DIRECTORY_SEPARATOR
	. 'frdl-projects'
	. \DIRECTORY_SEPARATOR 
	. 'testprojekt';

require_once $projectDir
	. \DIRECTORY_SEPARATOR 
	.'vendor'. \DIRECTORY_SEPARATOR .'autoload.php';
	

 $structure2 = [
                'home' => [
        
                ],
                'examples' => [],
        ];



  $structure = array_merge([
                'examples' => [
                    'test.php'    => 'some text content',
                    'other.php'   => 'Some more text content',
                    'Invalid.csv' => 'Something else',
                ],
                'an_empty_folder' => [],
                'badlocation.php' => 'some bad content',
                '[Foo]'           => 'a block device'
        ],
						  
		$structure2); 




/*
Application Bootstrap
*/

/* 

   //   Productional application usage: 
   
        \frdlweb\Level2App::getInstance('production',__DIR__ . \DIRECTORY_SEPARATOR. '..'. \DIRECTORY_SEPARATOR)->handle();

*/

          
               $container = \frdlweb\Level2App::getInstance('production', $projectDir. \DIRECTORY_SEPARATOR)
				   ->getContainer()
				   ;

$MountManager = $container->get(Manager::class); 

/*
Virtual
*/

 $MountManager::mount('web+virtual', 'home', 'virtual', [
  'fs.virtual.structure' => [
            'home' => [
                'index.html' => 'Hello, World!',

             ],
   ],
]);


 $mayFs =  vfsStream::setup('home', null, $structure);

echo '<pre>';
 vfsStreamWrapper::setQuota(new Quota(1028));
echo '</pre>';



file_put_contents('vfs://home/examples/test.php',
<<<HTNLCODE
<h1>Test Virtual File Index Contents...</h1>
HTNLCODE
				  
);



echo '<pre>';
echo htmlentities(file_get_contents('vfs://home/examples/test.php'));
echo '</pre>';




/*
Transactional
*/
\Covex\Stream\FileSystem::register("any-vfs-protocol", __DIR__);

file_put_contents("any-vfs-protocol://" . basename('.env.test'), "hahaha");
unlink("any-vfs-protocol://" . basename('.env.test'));
//\Covex\Stream\FileSystem::commit("any-vfs-protocol");
     


//\Covex\Stream\FileSystem::commit("any-vfs-protocol");
 echo file_exists('.env.test') ? "yes" : "no";

//\Covex\Stream\FileSystem::unregister("any-vfs-protocol");

file_put_contents('any-vfs-protocol://test-any-vfs-protocol.txt',
<<<HTNLCODE
<h1>Test any-vfs-protocol://test-any-vfs-protocol.txt Contents...</h1>
HTNLCODE
				  
);


echo 'any-vfs-protocol://test-any-vfs-protocol.txt<pre>';
echo htmlentities(file_get_contents('any-vfs-protocol://test-any-vfs-protocol.txt'));
echo '</pre>';


echo 'any-vfs-protocol://test-any-vfs-protocol.txt<pre>';
echo htmlentities(file_get_contents(__DIR__.'/test-any-vfs-protocol.txt'));
echo '</pre>';


\Covex\Stream\FileSystem::commit("any-vfs-protocol");






/*
 Protocol-Domain-Location Mappings 
*/
        $stream = new Stream('test', array('domain' => realpath(__DIR__.'/../../.frdl/')), true);
        $streamReadOnly = new Stream('test-read', array('domain' => realpath(__DIR__.'/../../.frdl/')), false);

        StreamManager::create()
                ->registerStream($stream)
                ->registerStream($streamReadOnly);


            file_put_contents('test://domain/written-file.ext', "contents\n");

echo '<pre>';
echo htmlentities(file_get_contents('test://domain/written-file.ext'));
echo '</pre>';



        $testsd = new Stream('test-local', ['till' => __DIR__], true);

        StreamManager::create()
                ->registerStream($testsd)
			
			;
			

file_put_contents('test-local://till/hallo.txt',
<<<HTNLCODE
<h1>Test Virtual Contents...test-local://till/hallo.txt -->  __DIR__/hallo.txt</h1>
HTNLCODE
				  
);



echo 'htmlentities(file_get_contents(\'test-local://till/hallo.txt\'))<pre>';
echo htmlentities(file_get_contents('test-local://till/hallo.txt'));
echo '</pre>';



echo 'htmlentities(file_get_contents(\'__DIR__/hallo.txt\'))<pre>';
echo htmlentities(file_get_contents(__DIR__.'/hallo.txt'));
echo '</pre>';




			
echo '<pre>';
print_r(stream_get_wrappers());
echo '</pre>';


			
			
echo '<pre>';
echo print_r(Manager::getMountsByPath('.'));
echo '</pre>';
		
			
//echo '<pre>';
//echo htmlentities(readdir('vfs://home'));
//echo '</pre>';



echo '$mayFs<pre>';
print_r($mayFs);
echo '</pre>';


````

# Result

````
test...
<h1>Test Virtual File Index Contents...</h1>
noany-vfs-protocol://test-any-vfs-protocol.txt
<h1>Test any-vfs-protocol://test-any-vfs-protocol.txt Contents...</h1>
any-vfs-protocol://test-any-vfs-protocol.txt
<h1>Test any-vfs-protocol://test-any-vfs-protocol.txt Contents...</h1>
contents
htmlentities(file_get_contents('test-local://till/hallo.txt'))
<h1>Test Virtual Contents...test-local://till/hallo.txt -->  __DIR__/hallo.txt</h1>
htmlentities(file_get_contents('__DIR__/hallo.txt'))
<h1>Test Virtual Contents...test-local://till/hallo.txt -->  __DIR__/hallo.txt</h1>
Array
(
    [0] => https
    [1] => ftps
    [2] => compress.zlib
    [3] => compress.bzip2
    [4] => php
    [5] => file
    [6] => glob
    [7] => data
    [8] => http
    [9] => ftp
    [10] => phar
    [11] => zip
    [12] => webfan
    [13] => frdl
    [14] => homepagespeicher
    [15] => frdlweb
    [16] => outshop
    [17] => startforum
    [18] => wehowski
    [19] => till
    [20] => safe
    [21] => nette.safe
    [22] => magic
    [23] => web+virtual
    [24] => vfs
    [25] => any-vfs-protocol
    [26] => test
    [27] => test-read
    [28] => test-local
)
Array
(
)
1
$mayFs
org\bovigo\vfs\vfsStreamDirectory Object
(
    [children:protected] => Array
        (
            [examples] => org\bovigo\vfs\vfsStreamDirectory Object
                (
                    [children:protected] => Array
                        (
                            [test.php] => org\bovigo\vfs\vfsStreamFile Object
                                (
                                    [content:org\bovigo\vfs\vfsStreamFile:private] => org\bovigo\vfs\content\StringBasedFileContent Object
                                        (
                                            [content:org\bovigo\vfs\content\StringBasedFileContent:private] => 
Test Virtual File Index Contents...

                                            [offset:org\bovigo\vfs\content\SeekableFileContent:private] => 16384
                                        )

                                    [exclusiveLock:protected] => 
                                    [sharedLock:protected] => Array
                                        (
                                        )

                                    [name:protected] => test.php
                                    [type:protected] => 32768
                                    [lastAccessed:protected] => 1591055997
                                    [lastAttributeModified:protected] => 1591055997
                                    [lastModified:protected] => 1591055997
                                    [permissions:protected] => 438
                                    [user:protected] => 10023
                                    [group:protected] => 1003
                                    [parentPath:org\bovigo\vfs\vfsStreamAbstractContent:private] => home/examples
                                )

                        )

                    [name:protected] => examples
                    [type:protected] => 16384
                    [lastAccessed:protected] => 1591055997
                    [lastAttributeModified:protected] => 1591055997
                    [lastModified:protected] => 1591055997
                    [permissions:protected] => 511
                    [user:protected] => 10023
                    [group:protected] => 1003
                    [parentPath:org\bovigo\vfs\vfsStreamAbstractContent:private] => home
                )

            [an_empty_folder] => org\bovigo\vfs\vfsStreamDirectory Object
                (
                    [children:protected] => Array
                        (
                        )

                    [name:protected] => an_empty_folder
                    [type:protected] => 16384
                    [lastAccessed:protected] => 1591055997
                    [lastAttributeModified:protected] => 1591055997
                    [lastModified:protected] => 1591055997
                    [permissions:protected] => 511
                    [user:protected] => 10023
                    [group:protected] => 1003
                    [parentPath:org\bovigo\vfs\vfsStreamAbstractContent:private] => home
                )

            [badlocation.php] => org\bovigo\vfs\vfsStreamFile Object
                (
                    [content:org\bovigo\vfs\vfsStreamFile:private] => org\bovigo\vfs\content\StringBasedFileContent Object
                        (
                            [content:org\bovigo\vfs\content\StringBasedFileContent:private] => some bad content
                            [offset:org\bovigo\vfs\content\SeekableFileContent:private] => 0
                        )

                    [exclusiveLock:protected] => 
                    [sharedLock:protected] => Array
                        (
                        )

                    [name:protected] => badlocation.php
                    [type:protected] => 32768
                    [lastAccessed:protected] => 1591055997
                    [lastAttributeModified:protected] => 1591055997
                    [lastModified:protected] => 1591055997
                    [permissions:protected] => 438
                    [user:protected] => 10023
                    [group:protected] => 1003
                    [parentPath:org\bovigo\vfs\vfsStreamAbstractContent:private] => home
                )

            [Foo] => org\bovigo\vfs\vfsStreamBlock Object
                (
                    [content:org\bovigo\vfs\vfsStreamFile:private] => org\bovigo\vfs\content\StringBasedFileContent Object
                        (
                            [content:org\bovigo\vfs\content\StringBasedFileContent:private] => a block device
                            [offset:org\bovigo\vfs\content\SeekableFileContent:private] => 0
                        )

                    [exclusiveLock:protected] => 
                    [sharedLock:protected] => Array
                        (
                        )

                    [name:protected] => Foo
                    [type:protected] => 24576
                    [lastAccessed:protected] => 1591055997
                    [lastAttributeModified:protected] => 1591055997
                    [lastModified:protected] => 1591055997
                    [permissions:protected] => 438
                    [user:protected] => 10023
                    [group:protected] => 1003
                    [parentPath:org\bovigo\vfs\vfsStreamAbstractContent:private] => home
                )

            [home] => org\bovigo\vfs\vfsStreamDirectory Object
                (
                    [children:protected] => Array
                        (
                        )

                    [name:protected] => home
                    [type:protected] => 16384
                    [lastAccessed:protected] => 1591055997
                    [lastAttributeModified:protected] => 1591055997
                    [lastModified:protected] => 1591055997
                    [permissions:protected] => 511
                    [user:protected] => 10023
                    [group:protected] => 1003
                    [parentPath:org\bovigo\vfs\vfsStreamAbstractContent:private] => home
                )

        )

    [name:protected] => home
    [type:protected] => 16384
    [lastAccessed:protected] => 1591055997
    [lastAttributeModified:protected] => 1591055997
    [lastModified:protected] => 1591055997
    [permissions:protected] => 511
    [user:protected] => 10023
    [group:protected] => 1003
    [parentPath:org\bovigo\vfs\vfsStreamAbstractContent:private] => 
)
````
