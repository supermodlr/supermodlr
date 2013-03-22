<?php defined('SYSPATH') or die('No direct script access.');
class Field_SupermodlrVersionhistory_ModelId extends Field  implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_String;
    public $name = 'modelId';
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
    public $validtestvalue = FALSE;
    public $private = FALSE;
    public $model = array (
  'model' => 'model',
  '_id' => 'Model_SupermodlrVersionHistory',
);
    public $readonly = FALSE;

}