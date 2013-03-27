<?php

trait Trait_FieldDataType_String {
    public function validate_datatype($value) {
        return (is_string($value) || (is_object($value) && method_exists($value, '__toString')));

    }
    
    public function set_value($value, $args = NULL)
    {
        if ($this->validate_datatype($value) === FALSE) 
        {
            throw new Exception('Invalid value, cannot set');
        }
        return (string) $value; 
    }    

    public function export_value($value, $args = NULL) 
    {
        return (string) $value;
    }


    public function storage_value($value, $args = NULL) 
    {
        return (string) $value;
    }    
}
