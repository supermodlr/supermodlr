<?php 

class supermodlr_framework_kohana extends supermodlr_framework_default {
	
	public function find_file($file) 
	{
		$path = Kohana::find_file('classes/supermodlr', $file);
		if (file_exists($path)) 
		{
			require_once $path;
			return TRUE;
		} 
		else 
		{
			return FALSE;
		}
	}
	
	public function get_view($template = 'default',$theme = 'default',$media = 'web')
	{
		return View::factory()->get_view($template,$theme, $media);
	}
	
	public function render($View)
	{ 
		return $View->render();
	}
	
	public function bind($View,$name,$value)
	{
		$View->bind($name,$value);
	}	
	
	public function prepare_input_value($value) 
	{
		return HTML::chars($value);
	}
	
	public function supermodlr_root()
	{
		return MODPATH.'supermodlr'.DIRECTORY_SEPARATOR;
	}
	
	public function saved_classes_root()
	{
		return APPPATH.'classes'.DIRECTORY_SEPARATOR;
	}	
}