<?php defined('SYSPATH') or die('No direct script access.');

Route::set('supermodlr_api', 'supermodlr/api/<model>(/<action>(/<id>(/<id_action>)))', array('model' => '[a-zA-Z0-9_]+', 'action' => '[a-zA-Z0-9_]+', 'id' => '\*|[a-zA-Z0-9_]+', 'id_action' => '[a-zA-Z0-9_]+'))
	->defaults(array(
		'controller' => 'Supermodlr_Api',
		'action'     => 'index',
	));

//include firebug for logging (@todo wrap this around some security)
require_once('lib/FirePHPCore/fb.php');

Event::register('shmvc_view_factory',array('Superutil','shmvc_view_factory'));