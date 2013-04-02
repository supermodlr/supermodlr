<?php defined('SYSPATH') or die('No direct script access.');

trait Trait_FieldDataType_Datetime {

	public function validate_datatype($value) {
		return ((is_object($value) && $value InstanceOf DateTime) || (is_numeric($value)) || (is_string($value) && strtotime($value) !== FALSE));

	}
	
	public function set_value($value, $args = NULL)
	{
		if ($this->validate_datatype($value) === FALSE) 
		{
			throw new Exception('Invalid value, cannot set');
		}
		if (is_object($value) && $value InstanceOf DateTime)
		{
			$Datetime = $value; 
		}
		else if (is_numeric($value)) 
		{
			$Datetime = new DateTime();
			$Datetime->setTimestamp($value);
		}
		else if (is_string($value) && strtotime($value) !== FALSE)
		{
			$Datetime = new DateTime($value);
		}

		$Timezone = new DateTimeZone(date_default_timezone_get());
		$Datetime->setTimezone($Timezone);
		return $Datetime;

	}      

	public function export_value($value, $args = NULL) 
	{
		return $value->format("c");
	}


	public function storage_value($value, $args = NULL) 
	{
		return $value;
	}
}
