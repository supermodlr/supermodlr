<?php

trait Trait_FieldDataType_Decimal {
    public function validate_datatype($value) {
        return is_float($value);

    }
}
