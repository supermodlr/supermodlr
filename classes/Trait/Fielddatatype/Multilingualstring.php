<?php

trait Trait_FieldDataType_Multilingualstring {
    public function validate_datatype($value) {
        return (is_string($value));

    }
}
