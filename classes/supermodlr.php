<?php defined('SYSPATH') or die('No direct script access.');

//complied file generated at installation/configuration.  
//	sets framework name and default database drivers
//	includes all files related to all fields and datatypes so that autoload isn't needed


abstract class supermodlr extends supermodlr_core {
	public static $__scfg = array(
		'drivers_config' => array(
				array(
						'name'     => 'mongo',
						'driver'   => 'supermodlr_mongodb',
						'host'     => '127.0.0.1',
						'port'     => '27017',
						'user'     => '',
						'pass'     => '',
						'dbname'   => '',
						'replset'  => FALSE,
						'safe'     => TRUE,
						'fsync'    => FALSE,
				)
		),		
		'framework_name' => 'kohana',
		
	);

}
