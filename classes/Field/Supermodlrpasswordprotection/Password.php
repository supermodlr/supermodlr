<?php defined('SYSPATH') or die('No direct script access.');

class Field_Supermodlrpasswordprotection_Password extends Field  implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_String;
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
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $validtestvalue = FALSE;
    public $private = TRUE;
   /* public $model = array (
  'model' => 'trait',
  '_id' => 'Trait_SupermodlrPasswordProtection',
); */
    public $readonly = FALSE;

}