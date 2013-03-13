<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Password extends Field  implements Interface_Fieldstorage, Interface_Fielddatatype
{
    use Trait_Fieldstorage_Single, Trait_Fielddatatype_String;
    public $name = 'password';
    public $description = 'Password';
    public $datatype = 'string';
    public $charset = 'UTF-8';
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $maxlength = '128';
    public $defaultvalue = FALSE;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $validtestvalue = FALSE;
    public $private = TRUE;
    public $model = array (
  'model' => 'trait',
  '_id' => 'Trait_Supermodlrpasswordprotection',
);
    public $readonly = FALSE;

}