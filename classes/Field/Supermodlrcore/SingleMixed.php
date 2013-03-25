<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_SingleMixed extends Field implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_Mixed;
    public $name = 'SingleMixed';
    public $label = 'SingleMixed';
    public $description = 'SingleMixed';
    public $datatype = 'mixed';
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $maxlength = 510;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $private = FALSE;
    public $readonly = FALSE;

}