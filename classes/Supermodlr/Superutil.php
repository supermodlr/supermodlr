<?php defined('SYSPATH') or die('No direct script access.');

class Supermodlr_Superutil {

	// @todo this needs to be abstracted into the framework
	public static function shmvc_view_factory(&$args)
	{
		$args['View'] = new Supermodlr_View($args['file'], $args['data'], $args['ext']);
	}

}