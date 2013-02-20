<?php

trait Trait_FieldDataType_Int {
    public function validate_datatype($value) {
        return is_int($value);

    }
}
