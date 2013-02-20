<?php

trait Trait_FieldDataType_Relationship {
    public function validate_datatype($value) {
        return (isset($value['model']) && isset($value['_id']) && count($value) === 2);

    }
}
