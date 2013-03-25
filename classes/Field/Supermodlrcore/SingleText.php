<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_SingleText extends Field  implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_String;
    public $name = 'SingleText';
    public $label = 'SingleText';
    public $description = 'SingleText';
    public $datatype = 'string';
    public $charset = 'UTF-8';
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $maxlength = 510;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $validtestvalue = '';
    public $private = FALSE;
    public $readonly = FALSE;

}