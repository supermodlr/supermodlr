<?php defined('SYSPATH') or die('No direct script access.');
class Field_Versionhistory_Changes extends Field  implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_Mixed;
    public $name = 'changes';
    public $description = 'Changes';
    public $datatype = 'mixed';
    public $storage = 'single';
    public $required = TRUE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $defaultvalue = FALSE;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $validtestvalue = FALSE;
    public $private = FALSE;
    public $model = array (
  'model' => 'model',
  '_id' => 'Model_VersionHistory',
);
    public $readonly = FALSE;

}