<?php defined('SYSPATH') or die('No direct script access.');

trait Trait_FieldDataType_Mixed {

	public function validate_datatype($value) {
		return TRUE;
	}

	public function set_value($value, $args = NULL)
	{
		if ($this->validate_datatype($value) === FALSE) 
		{
			throw new Exception('Invalid value, cannot set');
		}
		return $value;
	}

	public function export_value($value, $args = NULL) 
	{
		return $value;
	}

	public function storage_value($value, $args = NULL) 
	{
		return $value;
	}
}
