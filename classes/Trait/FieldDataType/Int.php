<?php defined('SYSPATH') or die('No direct script access.');

trait Trait_FieldDataType_Int {
	
    public function validate_datatype($value) {
        return is_int($value);
    }

    public function set_value($value, $args = NULL)
    {
        if ($this->validate_datatype($value) === FALSE) 
        {
            throw new Exception('Invalid value, cannot set');
        }
        return (int) $value; 
    }      

    public function export_value($value, $args = NULL) 
    {
        return (int) $value;
    }


    public function storage_value($value, $args = NULL) 
    {
        return (int) $value;
    }        
}
