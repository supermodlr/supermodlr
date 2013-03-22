<?php defined('SYSPATH') or die('No direct script access.');

class Field_SupermodlrCreatedUpdated_Created extends Field  implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_Datetime;
    public $name = 'created';
    public $description = 'Created Datetime';
    public $datatype = 'datetime';
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $defaultvalue = FALSE;
    public $nullvalue = FALSE;
    public $hidden = TRUE;
    public $private = FALSE;
  /*  public $model = array (
  'model' => 'trait',
  '_id' => 'Trait_SupermodlrCreatedUpdated',
); */
    public $readonly = TRUE;

}