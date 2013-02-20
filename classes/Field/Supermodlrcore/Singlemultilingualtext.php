<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Singlemultilingualtext extends Field implements Interface_Fieldstorage, Interface_Fielddatatype
{
    use Trait_Fieldstorage_Single , Trait_Fielddatatype_Multilingualstring;

    public $name = 'singlemultilingualtext';
    public $datatype = 'string';
    public $charset = 'UTF-8';
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $maxlength = '255';
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $private = FALSE;
    public $readonly = FALSE;

}