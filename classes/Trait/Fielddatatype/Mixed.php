<?php

trait Trait_FieldDataType_Mixed {
    public function validate_datatype($value) {
        return TRUE;

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
        return $value;
    }


    public function storage_value($value, $Model = NULL) 
    {
        return $value;
    }        
}
