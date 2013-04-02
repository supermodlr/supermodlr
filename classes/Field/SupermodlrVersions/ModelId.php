<?php defined('SYSPATH') or die('No direct script access.');
class Field_SupermodlrVersions_ModelId extends Field_Supermodlrcore_ModelId 
{
    public $name = 'ModelId';
    public $datatype = 'string';
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $extends = array (
  'model' => 'field',
  '_id' => 'Field_Supermodlrcore_ModelId',
);
    public $private = FALSE;
    public $model = array (
  'model' => 'trait',
  '_id' => 'Trait_SupermodlrVersions',
);
    public $readonly = FALSE;

}