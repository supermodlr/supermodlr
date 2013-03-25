<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_UserAccessTags extends Field implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Array, Trait_FieldDataType_String;
    public $name = 'useraccesstags';
    public $label = 'User Access Tags';
    public $description = 'User Access Tags';
    public $datatype = 'string';
    public $charset = 'UTF-8';
    public $storage = 'array';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $nullvalue = FALSE;
    public $hidden = TRUE;
    public $private = TRUE;
    public $readonly = FALSE;

}