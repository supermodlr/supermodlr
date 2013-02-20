<?php

trait Trait_FieldDataType_Time {
    public function validate_datatype($value) {
        return ((is_object($value) && $value InstanceOf DateTime) || (is_numeric($value)) || (is_string($value) && strtotime($value) !== FALSE));

    }
}
