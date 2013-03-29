<?php
/*
storage=array dataype=scalar:
[1,2,3]

storage=array dataype=object:
[{p1: "v1",p2: "v2"},{p1: "v3",p2: "v4"}]

storage=keyed_array dataype=scalar:
{k1: "v1", k2: "v2"}

storage=keyed_array dataype=object:
{k1: {p1: "v1",p2: "v2"}, k2: {p1: "v3",p2: "v4"}}
*/
class Supermodlr_Field {


   const NOT_SET = 'Supermodlr_FIELD_VALUE_NOT_SET';

   public $name = NULL; //app name of field (alpha-numeric)
   public $label = NULL;
   public $description = NULL;
   public $datatype = NULL; //string,int,float,timestamp,datetime,boolean,relationship,binary,resource,object.   datatype of object means that this field is related to a model and expects to embed the fields (based on storage value)
   public $multilingual = NULL; //TRUE|FALSE
   public $charset = NULL; //UTF-8 (if datatype = string)
   public $storage = NULL;//single|array
   public $required = NULL;//TRUE|FALSE|array indicates if this field must be on an object before it can be saved. array indicates a conditional that must be met if this is true or not.  array format: array('{$field_key'=> '{$value}') || array('$callback'=> array('method1','method2')) - method1/method2 must be methods on the model class and are sent 1 param containing the value or field::NOT_SET 
   public $unique = NULL;//TRUE|FALSE|array indicates if the value of this field can not be the same as any other value of the same field in the same data set.  if array, it will contain an array of all other field names that make the entry unique
   public $searchable = NULL;//TRUE|FALSE indicates if this field value should be available in text searches
   public $filterable = NULL;//TRUE|FALSE indicates if this field value should be filterable in queries (meaning it should be included and indexed)
   public $values = NULL;//array|NULL
   public $value = self::NOT_SET;
   public $raw_value = self::NOT_SET;  
   public $php_value = self::NOT_SET;     
   public $group = NULL;//NULL|string
   public $filters = NULL;
   public $maxlength = NULL;
   public $defaultvalue = NULL;//NULL set a default value
   public $nullvalue = TRUE; //TRUE if $default_value is null then the value should be set to NULL if no other value is set.  if False and $default_value is null, than a default value will not be set for this field on an object 
   public $validation = NULL;//array of validation rules
   public $templates = NULL;//input & display templates (format: array('input'=> 'input_template', 'display'=> 'display_template')
   public $template = NULL;//string used to store the rendered field view which is passed to the form view
   public $hidden = FALSE; //bool indicates if this field is hidden on forms or not
   public $extends = NULL;//name of field that this field extends
   //public $fields = NULL; //if this is a storage of type 'keyed_array' or an 'array' storage of 'datatype':object, this is an array of Field objects available to that object
   public $submodel = NULL; //if datatype = 'object', this field tells us which model to embed
   public $parentmodel = NULL;// if this field is assigned to model Address, and that model is assigned as a sub model to model "Company", this field exists on Address.field1 to reference "Company"
   public $parentfield = NULL; //indicates the parent field if this field is a sub field of a parent datatype:object field
   public $validtestvalue = NULL; //a value that should be valid.  used for test saves if there is no default
   public $invalidtestvalues = NULL; //a set of values that should fail validation. used for test validation
   public $pk = FALSE; //set tot true to indicate that this field is the primary key for the database
   public $access = NULL;// set to array to control access to this field. format: array('create'=>array(),'read'=>array(),'update'=>array(),'delete'=>array()).  admins are always allowed for all operations.
   public $private = NULL; //bool set to true if this field value should never be viewable in a public/non-admin interface (example: password, salt)

   public $model = NULL; //stores the model pk/class name for which model this field belongs to
   public $model_name = NULL; //stores the model name for this->model
   public $conditions = NULL; //controls conditional display for input forms and display. format: array('input'=> array('field1'=> 'value'), 'display'=> array()).  input/display array format is same as mongodb query syntax (supports: $and, $or, $ne, $not, $gt, $lt, $gte, $lte, $regex)
   public $readonly = NULL; //if true, after set for the first time, it can not be changed unless by admin via admin interface
   public $source = NULL; //array of models to look in for a valid entry.  only valid if dataype == 'relationship' format: array('models'=>array('model1','model2'), 'where'=> array(/* additional where clauses used when looking for valid values*/),'name_column' = 'name')
   public $stored = TRUE; // If FALSE. this field can be used and displayed like all other fields, but is never stored in any database
   public $owner = NULL; // If True, this indicates that this field relates the entire object to a user owner.  Either a PK field or a relationship
   //@todo encryption?
   
   public static $default_messages = array( //array of messages for various errors if we want to override the default error messages  @todo create messages in a language file, allow placement of :value in message.
      'required' => 'You must enter a value',
      'storage.single' => 'Incorrect data storage format.  Expecting single value.',
      'storage.array' => 'Incorrect data storage format.  Expecting array of values.',
      'storage.keyed_array' => 'Incorrect data storage format.  Expecting keyed array of values.',
      'datatype.string' => 'This field must be a string (characters).',
      'datatype.int' => 'This field must be an integer number.',
      'datatype.float' => 'This field must be an decimal number.',
      'datatype.timestamp' => 'This field must be a valid timestamp.',
      'datatype.datetime' => 'This field must be a valid datetime.',
      'datatype.boolean' => 'This field must be true or false.',
      'datatype.relationship' => 'This field must be a valid relationship.',
      'datatype.binary' => 'This field must be stored as raw data.',
      'datatype.resource' => 'This field must be stored as an accessible path to a resource',
      'datatype.object' => 'This field must be stored as an object.',
      'datatype.mixed' => 'Invalid entry.',
      'invalues'=> 'You must select a valid value from the list.',
      'nullvalue'=> 'This field may not be set as null.',
      'unique'    => 'An entry with this value already exists.',
   );
   

    /**
     * factory
     * 
     * @param mixed \Supermodlr Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
    public static function factory(Supermodlr $Model = NULL)
    {
        return new static($Model);
    }

    /**
     * __construct creates a field object instance.
     * 
     * @param mixed $model send a model relationship field value to bind a model to this field
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function __construct(Supermodlr $Model = NULL)
    {
    	// If no model object was passed and the set $model property is an array
        if ($Model === NULL	&& isset($this->model) && is_array($this->model) && isset($this->model['_id']))
        {
            $pk = $this->model['_id'];
            $class = 'Model_'.Supermodlr::get_name_case($this->model['model']);
            $Model = $class::factory();
            $pk_name = $Model->cfg('pk_name');
            $Model->$pk_name = $pk;
        }
        else if (is_array($Model) && isset($Model['_id']) && isset($Model['model']))
        {
            $pk = $Model['_id'];
            $class = 'Model_'.Supermodlr::get_name_case($Model['model']);
            $Model = $class::factory();
            $pk_name = $Model->cfg('pk_name');
            $Model->$pk_name = $pk;
        }

        // if a model was sent
        if ($Model instanceof Supermodlr)
        {
            // bind the model and model name to this field
            $this->model = $Model;
            $this->model_name = $Model->get_name();
        }
        //$this->value = self::NOT_SET;
        //$this->raw_value = self::NOT_SET;
    }
   

   //returns the model name based on the class name
   public function get_model() 
   {
      //if thie model property is set
      if (isset($this->model) && $this->model !== NULL && $this->model InstanceOf Supermodlr)
      {
         return $this->model;
      }
      //if the model property is not set, attempt to detect the model based on the class name
      else
      {
         //get the class name used to call this object
         $called_class = get_called_class();
         //find the model name from the called class, if set
         //if the class name is field_[{$model}_]{$field}         
         

         if (preg_match("/^field_([^_]+)_/i",$called_class,$matches))
         {
            if ($matches[1] !== Model_Field::scfg('core_prefix')) 
            {
               $model_class = Supermodlr::name_to_class_name($matches[1]);
               return $model_class::factory();
            }
            else
            {
               return NULL;
            }
         }
         //no model set
         else
         {
            return NULL;
         }
      }

   }

    /**
     * get_model_name returns the $model.name value of the model that is bound to this field, if any
     * 
     * @access public
     *
     * @return mixed name of model bound to this field, or NULL.
     */
    public function get_model_name()
    {
        if (isset($this->model) && $this->model instanceof Supermodlr) 
        {
            return $this->model->get_name();
        }
        else
        {
            return NULL;
        }
    }


    /**
     * @param mixed $value required field value to be validated
     * @param array $fieldset keyed array of all other values sent with this submission
     * @returns Status
     */
    public function validate($value, $fieldset = array()) 
   { 
      // check required.  if value is null and null value is not allowed, or this value was not set at all
      if ($this->required() && (($value === NULL && $this->nullvalue === FALSE) || $value === self::NOT_SET)) 
      {
         return new Status(FALSE,$this->message('required',$value));
      }
      //echo PHP_EOL.$this->name. ' sentvalue: ';
      //var_dump($value);     
      //check if the value is null but null is not an allowed value
      if ($value === NULL && $this->nullvalue === FALSE)
      {
         return new Status(FALSE,$this->message('nullvalue',$value));
      } 
      //if the value is null and null values are allowed
      else if ($value === NULL && $this->nullvalue === TRUE)
      {
         //skip all the other checks
         return new Status(TRUE);
      }
      
      //if the value is not set and its not required
      if ($value === self::NOT_SET) 
      {
         return new Status(TRUE);
      }
      
      if ($this->validate_storage($value) === FALSE)
      {
         return new Status(FALSE,$this->message('storage.'.$this->storage,$value));
      }

      if ($this->validate_datatype_values($value) === FALSE)
      {
         return new Status(FALSE,$this->message('datatype.'.$this->datatype,$value));
      }

      //check for value in values
      if ($this->values !== NULL && is_array($this->values))
      {
  
        if (is_array($value))
        {
          foreach ($value as $v)
          {
            if (!in_array($v,$this->values)) 
            {
               return new Status(FALSE,$this->message('invalues',$v));
            }
          }
        }
        else if (!in_array($value,$this->values)) 
        {
           return new Status(FALSE,$this->message('invalues',$value));
        }
      }

     
      $data = array();
      if (!empty($fieldset)) 
      {
      
         foreach ($fieldset as $Field) 
         {
            $data[$Field->name] = $Field->value;
         }
      } 

      $data[$this->name] = $value;

      // check other validation rules 
      if (is_array($this->validation) && count($this->validation) > 0) 
      {
         if ($this->storage == 'single') 
         {
            $values = array($value);
         }
         else 
         {
            $values = $value;
         }
         foreach ($values as $validate_value) 
         {
            $Validate = new Validation($data);
            $validation = array();
            foreach ($this->validation as $rule)
            { 
               if (is_array($rule)) 
               {
                  //if there are no arguments
                  if (!isset($rule[1]) || $rule[1] === array() || $rule[1] === NULL)
                  {
                     $rule[1] = NULL;
                  }
                  //if there are validate arguments, prepend the value
                  else
                  {
                     $rule[1] = array_merge(array(':value'),$rule[1]);
                  }
                  $validation[] = $rule;
               }
               else 
               {
                  $validation[] = array($rule); 
               }
            }

            $Validate->rules($this->name,$validation);
            
            if (!$Validate->check()) 
            {
               //$errors = $Validate->errors();

               return new Status(FALSE,'Invalid '.$this->name);
            }           
         }

      }

     
      return new Status(TRUE);
    }
   
   
    /**
    *
    * @return bool required property
    */
    public function required() 
    {
        //@todo if $this->required is not boolean, allow parsing of boolean logic to return bool required value
        return $this->required;
    }

    /**
    *
    * @return mixed default value property
    */
    public function defaultvalue() 
    {
        if (method_exists($this, 'get_defaultvalue'))
        {
            return $this->get_defaultvalue();
        }
        else
        {
            return $this->defaultvalue;
        }
    }

   /**
    *
    * @returns bool null property
    */
    public function nullvalue() 
   {
      return $this->nullvalue;
    }

    /**
     * value_isset
     * 
     * @access public
     *
     * @return mixed Value.
     */
    public function value_isset() {
      return (isset($this->value) && $this->value !== self::NOT_SET);
    }

    /**
    * @param mixed $value required field value to be filtered
    * @returns mixed value after all filters have been run against it
    */   
    public function filter($value) 
   {
      if (!is_null($this->filters) && is_array($this->filters)) 
      {
         foreach ($this->filters as $filter) 
         {
            if (is_callable($filter)) 
            {
               $value = $filter($value);
            }
         }
      }
      return $value;
    }
   
    /**
    * @param string $key send a key to return message
    * @returns string 
    */   
    public function message($key, $value = NULL) 
    { 
      if (isset($this->messages[$key])) 
      {
         return __($this->messages[$key],array(':value',$value));
      }
      else
      {
         if (isset(self::$default_messages[$key]))
         {
            return __(self::$default_messages[$key],array(':value',$value));
         }
         else
         {
            return __('Invalid '.$key,array(':value',$value));
         }

      }

    }
   
    /**
    * @todo figure out how to validate relationship and object against db driver(s) and possible use status and messages for each data type
    * @param mixed $value required Value whose data type needs to be validated
    * @param string $datatype a valid datatype string (string,int,float,timestamp,date,bool,binary,rel,object)
    * @returns bool true if $value is of $datatype variable type
    */       
    /*public function validate_datatype($value,$datatype) 
   {
      //string,int,float,unix timestamp,date,boolean,relationship,binary,resource,object,mixed
      if ($datatype == 'mixed') 
      {
         return TRUE;
      }
      else if ($datatype == 'string' && is_string($value)) 
      {
         return TRUE;
      } 
      else if ($datatype == 'int' && is_int($value)) 
      {
         return TRUE;
      } 
      else if ($datatype == 'float' && is_float($value)) 
      {
         return TRUE;
      } 
      else if ($datatype == 'timestamp' && is_int($value) && $value > 0) 
      {
         return TRUE;
      } 
      else if ($datatype == 'datetime' && ((is_object($value) && $value InstanceOf DateTime) || (is_numeric($value)) || (is_string($value) && strtotime($value) !== FALSE))) 
      {
         return TRUE;
      } 
      else if ($datatype == 'boolean' && is_bool($value)) 
      {
         return TRUE;
      } 
      else if ($datatype == 'binary' && is_binary($value) !== FALSE) 
      {
         return TRUE;         
      } 
      else if ($datatype == 'relationship' && isset($value['model']) && isset($value['_id']) && count($value) === 2) 
      {
         return TRUE;
      } 
      else if ($datatype == 'object') 
      {
         return TRUE;
      }     
      else 
      {
         return FALSE;
      }
   }*/

    /**
    * @param string $action required. 'create', 'read', 'update', 'delete'
    * @param array $permissions required.  an array containing any permission keyword setup by app, username, and any user groups that the user belongs to
    * @return bool true if $action is allowed
    */      
   public function access($action, $user_access_tags)
   {
      //no restrictions
      if ($this->access === NULL || !is_array($this->access))
      {
         return TRUE;
      }
      
      //admin, root, and owner are given all permissions
      if (in_array('admin',$user_access_tags) || in_array('owner',$user_access_tags))
      {
         return TRUE;
      }
      
      //if no permissions were given for this action, grant access
      if (!isset($this->access[$action]))
      {
         return TRUE;
      }
      
      //loop through all permissions
      foreach ($user_access_tags as $tag)
      {
         //if the permission key sent exists in the permission list for this operation
         if (in_array($tag,$this->access[$action]))
         {
            //allow the action
            return TRUE;
         }
      }
      //reject access
      return FALSE;
   }
   
    /**
    * @param array $array required
    * @returns bool true if this array is an assocative array, false if it is an index array
    */         
    public function is_assoc(array $array) 
   {
      // Keys of the array
      $keys = array_keys($array);

      // If the array keys of the keys match the keys, then the array must
      // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
      return array_keys($keys) !== $keys;
    }  

    // returns the name path for ths field including all parent fields
    public function path($delimeter = '.')
    { 
      $ParentField = $this->parentfield();
      //if ($ParentModel !== NULL)
      //{
         //load field.model and check for a parent model
        // $Model = new $model['_id'](); //@todo when a model is saved with a submodel field, we need to create the model specific submodel (model_company_address extends model_address) when that field is saved

      //}
      if ($ParentField !== NULL) 
      {
         //$Parent_model= new $this->parentmodel['_id']();
         $parent_path = $ParentField->path($delimeter);
         return $parent_path.$delimeter.$this->name;
      } else {
         return $this->name;
      }
    }

    public function parentmodel(Supermodlr $ParentModel = NULL)
    {
    	// Act as getter
    	if ($ParentModel === NULL)
    	{
    		return $this->parentmodel;
    	}
    	// Act as setter
    	else 
    	{
			$this->parentmodel = $ParentModel;
    	}
    }

    public function parentfield(Field $ParentField = NULL)
    {
    	// Act as getter
    	if ($ParentField === NULL)
    	{
    		return $this->parentfield;
    	}
    	// Act as setter
    	else 
    	{
			$this->parentfield = $ParentField;
    	}
    }

    public static function get_field_from_path($ParentModel, $field_path, $delim = '_')
    {
    	// Examples:
    	// model:       company
    	// submodel:    address
    	// parentfield: billaddress
    	// field:       city
    	if (strpos($field_path, $delim) !== FALSE)
    	{
    		// billaddress_city
    		// {$object_field}_{$object_field}_{$field}
    		// each {$object_field} maps to a model
    		$field_path_parts = explode($delim,$field_path);

    		// city
    		$field_name = Supermodlr::get_name_case(array_pop($field_path_parts));

    		// billaddress
			$field_parentfield_name = Supermodlr::get_name_case($field_path_parts[count($field_path_parts) - 1]);

			//get "submodel" (address)

//@todo get parent field class/object, then get submodel class/object

			$Parent_Field = 'Field_';

			foreach ($field_path_parts as $i => $part)
			{
				$field_path_parts[$i] = Supermodlr::get_name_case($part);
			}

    		$parent_model_name = Supermodlr::get_name_case($ParentModel->get_name());

    		// 
    		$found_class = FALSE;
    		// Look for class Field_{$Model}_{Object_field0}[_{Object_field1}..]_{$field} (Field_Company_Address_City)
    		// Look for class Field_{Object_field1's Model}_{Object_field1}_{$field} (Field_Address_Address_City)
    		// Look for class Field_{Object_field1's Model}_{$field} (Field_Address_City)    		
    		$class_paths = array(
    			'Field_'.$parent_model_name.'_'.implode('_',$field_path_parts).'_'.$field_name,
    			'Field_',
    		);
			if (class_exists($class))
			{
				$found_class = TRUE;
			}


    	}
    	else
    	{
    		$model_name = Supermodlr::get_name_case($Model->get_name());
    		$class_name = 'Field_'.$model_name.'_'.$field_path;
    		return $class_name::factory($ParentModel);
    	}
    }


    /**
    * @returns string containing javascript logic to be executed whenever any other field is updated to determine if this field should be shown, hidden, disabled, or enabled
    */       
    public function generate_conditions_javascript($field_id_prefix = NULL, $data_prefix = 'scope.', $container_id_suffix = '__container', $group_container_id_suffix = '__groupcontainer') 
    {
      //if no conditions are set
      if ($this->conditions == NULL || !is_array($this->conditions)) 
      {
         return '';
      }
      if ($field_id_prefix === NULL) 
      {
         $field_id_prefix = $this->model_name.'__field__';

      }
   
      $js = '';

      //loop through all commands and create js logic
      foreach ($this->conditions as $cmd => $logic) 
      {
         //skip non logic commands such as $hidden flags
         if (!is_array($logic)) {
            continue;
         }
         //convert basic mongo query to raw js
         $Query_parser = new Mongodbquery2js($logic);
         $Query_parser->set_var_prefix($data_prefix);
         $js_bool = $Query_parser->parse();   
         if ($cmd == '$showif')
         {
            $js .= "
   if (".$js_bool.") {
      $('#".$field_id_prefix.$this->name.$container_id_suffix."').show();
      return true;
   } else {
      $('#".$field_id_prefix.$this->name.$container_id_suffix."').hide();
      return true;
   }
            ";

         }
         else if ($cmd == '$hideif')
         {
            $js .= "
   if (".$js_bool.") {
      $('#".$field_id_prefix.$this->name.$container_id_suffix."').hide();
      return true;
   } else {
      $('#".$field_id_prefix.$this->name.$container_id_suffix."').show();
      return true;
   }
            ";
            
         }    
         else if ($cmd == '$groupshowif')
         {
            $js .= "
   if (".$js_bool.") {
      $('#".$field_id_prefix.$this->name.$group_container_id_suffix."').show();
      return true;
   } else {
      $('#".$field_id_prefix.$this->name.$group_container_id_suffix."').hide();
      return true;
   }
            ";

         }
         else if ($cmd == '$grouphideif')
         {
            $js .= "
   if (".$js_bool.") {
      $('#".$field_id_prefix.$this->name.$group_container_id_suffix."').hide();
      return true;
   } else {
      $('#".$field_id_prefix.$this->name.$group_container_id_suffix."').show();
      return true;
   }
            ";
            
         }              
         else if ($cmd == '$disableif')
         {
            $js .= "
   if (".$js_bool.") {
      $('#".$field_id_prefix.$this->name."').disable();
      return true;
   } else {
      $('#".$field_id_prefix.$this->name."').enable();
      return true;
   }
            ";
            
         }    
         else if ($cmd == '$enableif')
         {
            $js .= "
   if (".$js_bool.") {
      $('#".$field_id_prefix.$this->name."').enable();
      return true;
   } else {
      $('#".$field_id_prefix.$this->name."').disable();
      return true;
   }
            ";
            
         }  
         else if ($cmd == '$groupdisableif')
         {
            $js .= "
   if (".$js_bool.") {
      $('#".$field_id_prefix.$this->name." .input').disable();
      return true;
   } else {
      $('#".$field_id_prefix.$this->name." .input').enable();
      return true;
   }
            ";
            
         }    
         else if ($cmd == '$groupenableif')
         {
            $js .= "
   if (".$js_bool.") {
      $('#".$field_id_prefix.$this->name." .input').enable();
      return true;
   } else {
      $('#".$field_id_prefix.$this->name." .input').enable();
      return true;
   }
            ";
            
         }           
      }
      return $js;
    }

   /**
    * Formats a field value for writing into to a php class file.
    * @param mixed $value The value to be converted
    * @return mixed
    */
    public static function generate_php_value($value)
    {
        // If value is not set
        if (!isset($value) || $value === NULL)
            return 'NULL';

        elseif (is_bool($value))
            return ($value) ? 'TRUE' : 'FALSE';

        elseif (is_array($value))
            return var_export($value,TRUE);

        elseif (is_int($value))
            return $value;

        elseif (is_string($value) && strpos($value,"'") !== FALSE)
            return "'".str_replace("'","\\'",$value)."'";

        elseif (is_object($value) && $value instanceof Supermodlr)
            return "array('model'=> '".$value->get_name()."', '_id'=> '".$value->pk_value()."')";
        else
            return "'".$value."'";
    }
   

}