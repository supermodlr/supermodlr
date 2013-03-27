<?php

trait Trait_FieldDataType_MultilingualString {
    public function validate_datatype($value) {
        return (is_string($value));

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
