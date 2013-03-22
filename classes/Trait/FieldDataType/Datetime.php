<?php

trait Trait_FieldDataType_Datetime {
    public function validate_datatype($value) {
        return ((is_object($value) && $value InstanceOf DateTime) || (is_numeric($value)) || (is_string($value) && strtotime($value) !== FALSE));

    }
    public function set_value($value, $Model = NULL)
    {
        if ($this->validate_datatype($value) === FALSE) 
        {
            throw new Exception('Invalid value, cannot set');
        }
        return $value; 
    }      

    public function export_value($value, $Model = NULL) 
    {
        return $value->format("c");
    }


    public function storage_value($value, $Model = NULL) 
    {
        return $value;
    }        
}
