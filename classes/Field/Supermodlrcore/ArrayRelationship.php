<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_ArrayRelationship extends Field implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Array, Trait_FieldDataType_Relationship;
    public $name = 'ArrayRelationship';
    public $label = 'Array Relationship';
    public $description = 'Array Relationship';
    public $datatype = 'relationship';
    public $charset = 'UTF-8';
    public $storage = 'array';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $maxlength = 510;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $private = FALSE;
    public $readonly = FALSE;

}