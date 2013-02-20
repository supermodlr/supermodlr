<?php

trait Trait_Fieldstorage_Array {
    
    public function validate_storage($value = NULL) {
        return (is_array($value) && !$this->is_assoc($value));

    }

    public function validate_datatype_values($value) {
        foreach ($value as $i => $v)
        {
            return $this->validate_datatype($v);
        }
    }
}