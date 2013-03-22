<?php defined('SYSPATH') or die('No direct script access.');
class Field_Versionhistory_Modelid extends Field  implements Interface_Fieldstorage, Interface_Fielddatatype
{
    use Trait_Fieldstorage_Single, Trait_Fielddatatype_String;
    public $name = 'modelid';
    public $description = 'Model ID';
    public $datatype = 'string';
    public $charset = 'UTF-8';
    public $storage = 'single';
    public $required = TRUE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $maxlength = 12;
    public $defaultvalue = FALSE;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $validtestvalue = FALSE;
    public $private = FALSE;
    public $model = array (
  'model' => 'model',
  '_id' => 'Model_Versionhistory',
);
    public $readonly = FALSE;

}