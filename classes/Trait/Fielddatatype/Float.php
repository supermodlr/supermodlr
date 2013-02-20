<?php

trait Trait_FieldDataType_Float {
    public function validate_datatype($value) {
        return is_float($value);

    }
}
