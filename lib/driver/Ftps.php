<?php
namespace frdl\mount\driver;

use frdl\mount\Manager;
use frdl\mount\Driver;
use frdl\mount\Exception;

use frdl\mount\driver\Ftp;

use frdl\ContextContainer;

/**
 * MagicMounter, by Marvin Janssen (http://marvinjanssen.me), released in 2017.
 *
 * The FTPS magic driver provides a transparent FTPS transport. It extends \MagicMounter\driver\Ftp
 */
class Ftps extends Ftp
{
	protected $options;
	
	
	
	public function __construct( $options)
		{
		
				
		$this->options=(!is_object($options) || true!==$options instanceof ContextContainer)
			? ContextContainer::create($options, '${', '}')
			: $options	
			;
		
		
		
		
		if (!extension_loaded('openssl') || !function_exists('ftp_ssl_connect'))
			throw new Exception('OpenSSL extension not available',103);
      
		  parent::__construct($this->options);
		}

	protected function ftp_connect($host,$port,$timeout)
		{
		  return ftp_ssl_connect($host,$port,$timeout);
		}
	}
