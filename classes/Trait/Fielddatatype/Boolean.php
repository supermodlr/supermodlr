<?php

trait Trait_FieldDataType_Boolean {
    public function validate_datatype($value) {
        return is_bool($value);

    }
}
