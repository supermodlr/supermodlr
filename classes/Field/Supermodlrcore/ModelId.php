<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_ModelId extends Field  implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_String;
    public $name = 'ModelId';
    public $label = 'Model ID';
    public $description = 'Model ID';
    public $datatype = 'string';
    public $charset = 'UTF-8';
    public $storage = 'single';
    public $required = TRUE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $maxlength = 12;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $private = FALSE;
    public $readonly = FALSE;

}