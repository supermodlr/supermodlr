<?php

trait Trait_FieldDataType_Float {
    public function validate_datatype($value) {
        return is_float($value);

    }

    public function set_value($value, $Model = NULL)
    {
        if ($this->validate_datatype($value) === FALSE) 
        {
            throw new Exception('Invalid value, cannot set');
        }
        return (float) $value; 
    }      

    public function export_value($value, $Model = NULL) 
    {
        return (float) $value;
    }


    public function storage_value($value, $Model = NULL) 
    {
        return (float) $value;
    }        
}
