<?php 

class Supermodlr_Framework_Kohana extends Supermodlr_Framework_Default {
	
	private $config = NULL;

	public function load_config($file = NULL) 
	{
		if ($this->config === NULL) 
		{
				$this->config = Kohana::$config->load('supermodlr');
		}
		return (array) $this->config;
	}

	public function prepare_input_value($value) 
	{
		return HTML::chars($value);
	}
	
	public function Supermodlr_root()
	{
		return MODPATH.'supermodlr'.DIRECTORY_SEPARATOR;
	}
	
	public function saved_classes_root()
	{
		return APPPATH.'classes'.DIRECTORY_SEPARATOR;
	}	

	//config file

	//bind access tags
}