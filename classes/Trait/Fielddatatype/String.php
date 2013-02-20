<?php

trait Trait_FieldDataType_String {
    public function validate_datatype($value) {
        return (is_string($value));

    }
}
