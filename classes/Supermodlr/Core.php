<?php
/**
* Supermodlr_Core
*
* @uses     
*
* @category Core
* @package  Supermodlr
* @author   Justin Shanks <jshanman@gmail.com>
*/    
abstract class Supermodlr_Core {

    // We want to use the core Supermodlr_Trait_Supertrait so that only traits use the Trait_Supertrait trait
    use Supermodlr_Trait_Supertrait;
    
    // Static config for vars that apply to all data types and can be loaded once
    protected static $__scfg = array();

    // Object config
    protected $__cfg = array();

    /**
    * core construct
    * 
    * Used for creating or loading new objects
    * @param mixed $id = NULL
    * @param array $data = NULL
    * @return NULL
    */
    public function __construct($id = NULL, $data = NULL)
    {
        //init framework
        $this->init_framework();

        //init config
        $this->init_cfg();

        //get primary id field
        $pk_name = $this->cfg('pk_name');

        //if an id was sent
        if (!is_null($id))
        {
            //set primary key column
            $this->$pk_name = $id;
        }

        //call construct event
        $this->model_event('construct',$this);
          //if we are loading an existing object
          if ($id !== NULL)
          {
                //create an object by numeric id
                if ($data === NULL)
                {
                     $data = $this->select_by_id($id);
                }
                //if we found this object in the database
                if ($data)
            {
                     //load column values and field values
                $this->load($data);
                $this->cfg('loaded_data',$this->to_array(FALSE));
                $this->cfg('loaded',TRUE);
            }
            else
            {
                $this->cfg('loaded',FALSE);
                //@todo create custom Supermodlr exceptions
                //throw new Exception('Cannot load object using Id: '.var_export($id,TRUE));
            }
        }
        //we are creating a new entry for this object
        else
        {
            $this->cfg('loaded',FALSE);
            $this->cfg('new_object',TRUE);
        }
        //init all traits.  loads traits from model and from data (if stored)
        //$this->init_traits();
        //call event for construct end
        $this->model_event('construct_end',$this);
    }

    /**
    * return lower cased called class name of this model
    */
    public static function get_model_class()
    {
        return ucfirst(strtolower(get_called_class()));
    }

    /**
    * return lower cased called class name of this model
    */
    public static function get_name()
    {
          return preg_replace('/^model_/i','',strtolower(get_called_class()));
    }

    //given a name, return the expeted model name (uppercase all words seperated by '_' and lowercase everything else. prefix with 'Model_')
    public static function name_to_class_name($name)
    {
          $name = static::get_name_case($name);
          return 'Model_'.$name;
    }

    //returns just the model name given a model class name
    public static function get_name_from_class($class)
    {
        //return everything after 'model_'
        $parts = explode('_',$class);
        return strtolower(array_pop($parts));
    }
    
    /**
        * sets up all initial cfg parameters
        *        field objects are stored staticly per class
        *        driver keys are stored staticly per class
        *        driver objects are stored by key staticly once on the core model
        */
    public static function init_cfg()
    {
        static::$__scfg['init_cfg'] = TRUE;
          
        //call events to init config for the datatype
          
        //static::model_event('init_cfg');

        //get framework
        $Framework = static::get_framework();


        $name = static::get_name();

        //setup default name config
        static::scfg('name',$name);

        // Load all direct traits (and their traits), set all scfg values if they are not yet set
        $traits = static::get_traits();

        // Loop through all traits and traits of traits 
        foreach ($traits as $trait)
        {
            // Get the name of this trait
            $trait_name = $trait::get_trait_name();

            // Get the name of the property that we expect to find scfg values on
            $scfg_prop = '__'.$trait_name.'__scfg';

            // If scfg property is set and is an array
            if (isset($trait::$$scfg_prop) && is_array($trait::$$scfg_prop))
            {
                // Get the static __$trait__scfg property from the trait class
                $trait_scfg = $trait::$$scfg_prop;

                // Loop through all scfg values on this trait
                foreach ($trait_scfg as $trait_scfg_key => $trait_scfg_value)
                {
                    // If this scfg key is not set on this class
                    if (!isset(static::$__scfg[$trait_scfg_key]))
                    {
                        static::$__scfg[$trait_scfg_key] = $trait_scfg_value;
                    }
                    //if it is set and is an array, merge recursive
                    else if (is_array(static::$__scfg[$trait_scfg_key]) && is_array($trait_scfg_value))
                    {
                        // static::$__scfg should override any values set in $trait_scfg_value
                        static::$__scfg[$trait_scfg_key] = array_merge_recursive($trait_scfg_value,static::$__scfg[$trait_scfg_key]);
                    }
                }
            }
        }

        // loop through class tree (skipping this class)

            // loop through all parent::__scfg values and set them on self::__scfg if not already set (@todo decide recursive merge??)

        //setup default db_name config (table or collection name for this datatype)
        if (!isset(static::$__scfg['db_name']))
        {
            static::scfg('db_name',$name);
        }

        //setup default primary_key (pk) column config
        if (!isset(static::$__scfg['pk_name']))
        {
            $pk = '_id';
            static::scfg('pk_name',$pk);
        }

        //setup default primary_key (pk) column config
        if (!isset(static::$__scfg['pk_field']))
        {
            $pk_name = static::get_name_case(static::scfg('pk_name'));
            //default field class name for pk field
            $pk_class = 'Field_'.static::get_name_case($name).'_'.$pk_name;
            static::scfg('pk_field',$pk_class);
        }

        //setup default cache setting
        if (!isset(static::$__scfg['read_cache']))
        {
            static::scfg('read_cache',FALSE);
        }

        //setup driver names for this datatype
        if (!isset(static::$__scfg['drivers']))
        {
            //default drivers setup in the app model class file generated on install
            $drivers_config = static::scfg($name.'.drivers_config');
            if ($drivers_config === NULL)
            {
              $drivers_config = static::scfg('drivers_config');
            }
            if (is_array($drivers_config))
            {
                $primary_driver = NULL;
                $non_primary = array();
                foreach ($drivers_config as $i => $driver)
                {
                    //if there is only one driver, make it primary
                    if (count($drivers_config) == 1)
                    {
                        $driver['primary'] = TRUE;
                        $drivers_config[$i] = $driver;
                    }
                    //if this driver is marked as primary
                    if (isset($driver['primary']) && $driver['primary'] == TRUE || $i === 'primary')
                    {
                        //store driver object in primary driver var
                        $primary_driver = $Framework->get_driver($driver['driver'],$driver);
                    }
                    //this is not marked as the primary driver
                    else
                    {
                        //store this db object in a non primary var
                        $non_primary[] = $Framework->get_driver($driver['driver'],$driver);
                    }
                }
                //if there is more than one db driver, ensure the primary is listed first
                if (count($drivers_config) > 1)
                {
                   //if no primary driver was set in the config
                   if ($primary_driver === NULL)
                   {
                       //set first database in config list as primary
                       $drivers_config[0]['primary'] = TRUE;
                       $drivers = $non_primary;
                   }
                   //if a primary was set, ensure it is first
                   else
                   {
                       $drivers = array();
                       $drivers[] = $primary_driver;
                       $drivers = array_merge($drivers,$non_primary);
                   }
                }
                //if there is only one db driver
                else
                {
                    //set the drivers var as the primary driver
                    $drivers = array();
                    $drivers[] = $primary_driver;
                }
                //store all db driver objects
                static::scfg('drivers',$drivers,TRUE);
                //re-save drivers config in case there were any changes
                static::scfg('drivers_config',$drivers_config);
            }
            //@todo add in config option for additional drivers (that are added after any drivers set by the parent) instead of overriding the parent driver_config OR make a way to make conf options on multiple levels merge (if they are an array)
        }

        // default access tags
        if (!isset(static::$__scfg['model_access_rules']))
        {
            static::scfg('model_access_rules',array(
                'create' => array('auth'),
                'read'    => array('auth'),
                'update' => array('owner'),
                'delete' => array('owner'),
                'query'  => array('admin'),
            ));
        }
        //default user access tags to anon access
        if (!isset(static::$__scfg['user_access_tags']))
        {
            static::scfg('user_access_tags',array('anon'));
        }
    }

    /**
     * gets or sets config. used to store variables that apply to an instantiated object
     */
    public function cfg($key,$value = NULL)
    {
        if ($value === NULL)
        {
            if (isset($this->__cfg[$key]))
            { 
                return $this->__cfg[$key];
            }
            else
            { 
                return $this->scfg($key);
            }
        }
        else
        {
            $this->__cfg[$key] = $value;
        }
    }
    
    /**
     * gets or sets static config. used to store variables that are static and apply to all objects
     */
    public static function scfg($key,$value = NULL)
    {
        //if we are not setting a value, retrieve it
        if ($value === NULL)
        {
            //init config if it hasn't been init yet
            if (!isset(static::$__scfg['init_cfg']))
            { 
                static::init_cfg();
            }
            if (isset(static::$__scfg[$key]))
            { 
                 return static::$__scfg[$key];
            }
            else
            {
                 return static::get_scfg_value($key);;
            }
        //store this config value
        } else {

            static::$__scfg[$key] = $value;
        }
    }
    
    /**
     * returns a scfg value, checks all classes in the tree
     */
    public static function get_scfg_value($key)
    {
    //init config if it hasn't been init yet
        if (!isset(static::$__scfg['init_cfg']))
        {
            static::init_cfg();
        }        
        //get all classes in extension tree
        $classes = static::get_class_tree();

        //loop through all classes
        foreach ($classes as $class)
        {
            // Skip self
            if ($class === get_called_class()) continue;

            //check scfg for this class
            if (isset($class::$__scfg[$key]))
            {
                 return $class::$__scfg[$key];
            }
        }
        return NULL;
    }

    // loads config for connected database(s) and framework specific functions
    public static function init_framework() {
        //if the framework hasn't already been loaded
        if (!isset(Supermodlr::$__scfg['framework']) || Supermodlr::$__scfg['framework'] === NULL)
        {
            //choose framework
            $Framework = Supermodlr::load_framework();
            //store framework class
            Supermodlr::$__scfg['framework'] = $Framework;
            //get config array
            $config = $Framework->load_config('supermodlr');
            //merge config into existing scfg
            if (is_array($config) && !empty($config))
            {
                Supermodlr::$__scfg = array_merge_recursive(Supermodlr::$__scfg,$config);
            }
        }
    }

    // looks for a framework_name property.  defaults to kohana
    public static function load_framework()
    {
        $called_class = get_called_class();
        $class_tree = $called_class::get_class_tree();
        $framework = NULL;
        foreach ($class_tree as $class)
        {
          //look for framework property set by custom Supermodlr file
          if (isset($class::$__scfg['framework_name']) && $class::$__scfg['framework_name'] !== NULL)
          {
              $framework = $class::$__scfg['framework_name'];
              break ;
          //fallback on default
          }
        }
        if ($framework === NULL)
        {
            $framework = 'Default';
        }
        //create class name
        $class = 'Framework_'.ucfirst(strtolower($framework));
        
        //return new instance
        $Framework = new $class();
        return $Framework;
    }

    /**
     * returns the framework object from the static config
     */
    public static function get_framework()
    {
        if (!isset(Supermodlr::$__scfg['framework']))
        {
          static::init_framework();
        }
        return Supermodlr::$__scfg['framework'];
    }

    /**
     * loops through all fields and sets the valid test values if there are any.
     */
    public function set_test_valid_values()
    {
        $fields = $this->get_fields();
        foreach ($fields as $Field)
        {
            //get field key
            $field_key = $Field->name;
            //if this field isn't the pk
            if ($field_key != $this->cfg('pk_name'))
            {
                //set the test value
                $this->$field_key = $Field->validtestvalue;
            }
        }
    }

    public static function get_name_case($name)
    {
          $name = str_replace('_',' ',$name);
          $name = ucwords(strtolower($name));
          return str_replace(' ','_',$name);
    }

    /**
     * selects an entry of data based on primary key from the primary db
     */
    public function select_by_id($id)
    {
        $Driver = $this->get_primary_db();
        $db_name = $this->cfg('db_name');
        $pk_name = $this->cfg('pk_name');
        $row = static::query(array(
            'where'   => array($pk_name => $id),
            'limit'   => 1,
            'allowed' => TRUE,
        ));
        if ($row)
        {
            //return first result
            return $row;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * returns the primary db object
     */
    public function get_primary_db()
    {
        $drivers = $this->cfg('drivers');
        return $drivers[0];
    }

    /**
     *
     */
    public static function get_fields($for_model = NULL)
    {

        // Get class name as called
        $called_class = get_called_class();

        if ($for_model === NULL)
        {
            $for_model = $called_class;
        }

        //get model name
        $model_name = $called_class::get_name();

        //return fields if already loaded
        if (static::scfg('fields') !== NULL)
        {
            return static::scfg('fields');

        //load fields listed in field_keys
        }
        else if (static::scfg('field_keys') !== NULL)
        {

            //look on this model for any field keys
            $field_keys = static::scfg('field_keys');

            $fields = array();

            $traits = static::get_traits();

            //loop through all field keys
            foreach ($field_keys as $field_name)
            {
                // Detect field class name
                $field_class_name = static::get_name_case($field_name);

                // Detect model class name
                $model_class_name = static::get_name_case($model_name);

                // Build full field class name @todo move this to a central function
                $fieldclass = 'Field_'.$model_class_name.'_'.$field_class_name;

                $corefieldclass = 'Field_Supermodlrcore_'.$field_class_name;

                // Ensure the class exists.  If it doesn't exist, we assume a child model has a class for this field
                if (class_exists($fieldclass))
                {
                    // Assign the instantiated field to the array of fields.  Create the field with $for_model's context
                    $fields[$field_name] = new $fieldclass(array('model'=> 'model', '_id'=> $for_model));
                }
                //try all direct traits or core
                else 
                {
                    $found_in_traits = FALSE;

                    //loop through all traits
                    foreach ($traits as $trait)
                    {
                        //get the trait name
                        $trait_class_name = static::get_name_case(static::get_trait_name($trait));

                        // create expected trait field class name
                        $traitfieldclass = 'Field_'.$trait_class_name.'_'.$field_class_name;

                        // if the trait field class exists and is a valid supermodlr field
                        if (class_exists($traitfieldclass) && is_subclass_of($traitfieldclass,'Field'))                          
                        {

                            // assign the field
                            $fields[$field_name] = new $traitfieldclass(array('model'=> 'model', '_id'=> $for_model));

                            // mark it as found
                            $found_in_traits = TRUE;

                            // don't look for field in the rest of the traits (if any)
                            break ;
                        }
                    }

                    // if this field was not found on direct traits or their traits and the core version exists
                    if (!$found_in_traits && class_exists($corefieldclass))
                    {
                        // use the core version of the field
                        $fields[$field_name] = new $corefieldclass(array('model'=> 'model', '_id'=> $for_model));
                    }
                    //could not find a valid field
                    else
                    {
                        fbl($fieldclass .' class not found');
                    }
                }
            }

            // Get the direct parent of this class
            $parent_class = get_parent_class($called_class);

            // If there is a parent
            if ($parent_class !== FALSE)
            {
                // Call get_fields on the parent (which will call it on it's parent, if any)
                $parent_fields = $parent_class::get_fields($for_model);

                // Merge in results, with the current fields overriding any below it
                $fields = array_merge($parent_fields,$fields);                
            }

            //look at all traits for fields @todo figure out if traits should be before or after parents and how they can be ordered in case of conflicts
            //store created fields once for each model
            static::scfg('fields',$fields);
            return $fields;
        } 
        else 
        {
            return array();
        }
    }

    public static function get_class_tree()
    {
        $called_class = get_called_class();
        $classes = array();
        $classes[] = $called_class;
        $classes = array_merge($classes,class_parents($called_class));
        return $classes;
    }

    /**
     * owner_field finds and returns the name of the field in $self::fields that is marked as the "owner" field.  The Owner field is used to link this row to a bound user for permissions
     * 
     * @param mixed $field_name send a field_name to set as the owner field or NULL to get (or detect) the current owner field.
     *
     * @access public
     * @static
     *
     * @return string|NULL $field.name of the field marked as the owner field for this model or NULL if there is none
     */
    public static function owner_field($field_name = NULL)
    {
        //if acting as a getter
        if ($field_name === NULL)
        {
            // Loop through all fields on this model and look for a field with a property of 'owner=true'. 
            if (static::scfg('owner_field') === NULL)
            {
                // get all fields
                $fields = static::get_fields();
                //loop through all fields
                foreach ($fields as $Field)
                {
                    //if a field marked as "owner" is found
                    if (isset($Field->owner) && $Field->owner === TRUE)
                    {
                        //save this field key as the owner field
                        static::scfg('owner_field',$Field->name);
                        break ;
                    }
                }
            }      
            return static::scfg('owner_field');            
        }
        // Act as a setter
        else
        {
            static::scfg('owner_field',$field_name);
        }

    }


    /**
     * returns TRUE if this object was loaded
     */
    public function loaded()
    {
        return $this->cfg('loaded');
    }

    /**
     * returns TRUE if new_object is true
     */
    public function is_new()
    {
        return $this->cfg('new_object');
    }

    /**
     * returns value of pk column or NULL if not set
     */
    public function pk_value()
    {
         $pk_name = $this->cfg('pk_name');
          return (isset($this->$pk_name)) ? $this->$pk_name : NULL;
    }

    /**
     * detects data changes compared to loaded data
     */
    public function changes($new_data = NULL)
    {
        if ($new_data === NULL)
        {
            $new_data = $this->to_array();
        }
        $old_data = $this->cfg('loaded_data');
        $args = array('new_data'=> &$new_data,'old_data'=> &$old_data, 'this'=> $this);
        $this->model_event('changes',$args);
        //unset any vars previously set
        unset($this->__cfg['updated']);
        unset($this->__cfg['added']);
        unset($this->__cfg['removed']);
        unset($this->__cfg['changed']);
        $this->changes_recursive($old_data,$new_data);
        return array('updated' => $this->updated(), 'added'=> $this->added(), 'removed'=> $this->removed(), 'changed'=> $this->changed());
    }

    /**
     * recursive function to detect and record adds, updates, and removes of data on this object
     */
    public function changes_recursive($old_data,$new_data,$key = '')
    {
        if ($this->loaded() && is_array($old_data))
        {
            foreach ($old_data as $col => $val)
            {
                if ($key != '')
                {
                    $sub_key = $key.'.'.$col;
                }
                else
                {
                    $sub_key = $col;
                }
                //if the data has somehow changed
                if (!isset($new_data[$col]) || $old_data[$col] != $new_data[$col])
                {
                    //if this value is in old_data but not in new data, it was removed
                    if (!isset($new_data[$col]) || (is_null($new_data[$col]) && !is_null($old_data[$col])))
                    {
                        $this->removed($sub_key,$old_data[$col]);
                        $this->changed($sub_key,'removed',NULL,$old_data[$col]);
                    //if this data is in both old and new, but it is different, then it is updated
                    }
                    else
                    {
                        $this->updated($sub_key,$new_data[$col],$old_data[$col]);
                        $this->changed($sub_key,'updated',$new_data[$col],$old_data[$col]);
                     }
                    if (is_array($old_data[$col]))
                    {
                        if (isset($new_data[$col]) && is_array($new_data[$col]))
                        {
                            $send_new_data = $new_data[$col];
                        }
                        else
                        {
                            $send_new_data = array();
                        }
                        $this->changes_recursive($old_data[$col],$send_new_data,$sub_key);
                    }
                }
            }
        }
        if (is_array($new_data))
        {
            //look for new additions that don't exist in old_data
            foreach ($new_data as $col => $val)
            {
                if ($key != '')
                {
                    $sub_key = $key.'.'.$col;
                }
                else
                {
                    $sub_key = $col;
                }
                if (!isset($old_data[$col]) || (is_null($old_data[$col]) && !is_null($new_data[$col])))
                {
                    $this->added($sub_key,$new_data[$col]);
                    $this->changed($sub_key,'added',$new_data[$col]);
                    $this->changes_recursive(array(),$new_data[$col],$sub_key);
                }
            }
        }
    }

    /**
     * runs filters defined in the data model
     */
    public function filter($fields = NULL, &$pointer = NULL)
    {
          //get default field set if none was sent
          if ($fields === NULL)
        {
                $fields = $this->get_fields();
          }
          //use object as pointer if no pointer was sent
          if ($pointer === NULL)
        {
                $pointer = $this;
          }
          //loop through each defined field
          foreach ($fields as $field_class => $Field)
        {
            //if this is a relationship reference
            if (is_array($Field) && isset($Field['_id']) && isset($Field['model']) && count($Field) === 2)
            {
                //load the field
                $Field = new $Field['_id']();
            }
            //get field key
            $field_key = $Field->name;
                //if filter(s) were assigned to this data point
                if (isset($Field->filters) && is_array($Field->filters))
            {
                //if this value is set (do we not run filters if the target of the filter is not set?? makes sense to me now)
                if ((is_array($pointer) && isset($pointer[$field_key])) || (is_object($pointer) && isset($pointer->$field_key)))
                {
                    //loop through each filter
                    foreach ($Field->filters as $method)
                    {
                        //if the method is an object/method pair
                        if (is_array($method))
                        {
                            //assign the result of the filter to the pointer->field_key
                            if (is_array($pointer))
                            {
                                $args = array('value'=> $pointer[$field_key], 'key'=> $field_key, 'field'=> $Field, 'object'=> $this);
                                $pointer[$field_key] = call_user_func($method,$args);
                            }
                            else if (is_object($pointer))
                            {
                                $args = array('value'=> $pointer[$field_key], 'key'=> $field_key, 'field'=> $Field, 'object'=> $this);
                                $pointer->$field_key = call_user_func($method,$args);
                            }
                        }
                        //if the filter is a normal php function
                        else if (is_callable($method))
                        {
                            //assign the result of the filter to the pointer->field_key
                            if (is_array($pointer))
                            {
                                $pointer[$field_key] = call_user_func($method,$pointer[$field_key]);
                            }
                            else if (is_object($pointer))
                            {
                                $pointer->$field_key = call_user_func($method,$pointer->$field_key);
                            }
                        }
                        //if the filter is a normal php function
                        else if (function_exists($method))
                        {
                            //assign the result of the filter to the pointer->field_key
                            if (is_array($pointer))
                            {
                                $pointer[$field_key] = $method($pointer[$field_key]);
                            }
                            else if (is_object($pointer))
                            {
                                $pointer->$field_key = $method($pointer->$field_key);
                            }
                        }
                    }
                }
                }
            //unset non stored field values
            if ($Field->storage === FALSE) {
                if (is_array($pointer))
                {
                     unset($pointer[$field_key]);
                }
                else if (is_object($pointer))
                {
                     unset($pointer->$field_key);
                }
                //skip to next field (since stored===false fields do not need to be filtered)
                continue;
            }
            //if this field is an object or a set of objects, loop through each sub-object to look for filters
            if (($Field->storage == 'keyed_array' || $Field->datatype == 'object') && $Field->submodel !== NULL && is_array($Field->submodel))
            {
                $recursive = FALSE;
                if (is_array($pointer) && isset($pointer[$field_key]))
                {
                     $send_pointer = &$pointer[$field_key];
                     $recursive = TRUE;
                }
                else if (is_object($pointer) && isset($pointer->$field_key))
                {
                     $send_pointer = &$pointer->$field_key;
                     $recursive = TRUE;
                }
                //recursivly run this method to filter any sub-object field values
                if ($recursive)
                {
                    //an array of objects or a keyed array of objects
                    if (($Field->storage == 'keyed_array' || $Field->storage == 'array') && $Field->datatype == 'object')
                    {
                         $array_set = $send_pointer;
                        foreach ($array_set as $i => $object)
                        {
                            $this_send_pointer = &$send_pointer[$i];
                            $this->filter($Field->fields,$this_send_pointer);
                        }
                     }
                    //a single object
                    else if ($Field->datatype == 'object')
                    {
                        $this->filter($Field->fields,$send_pointer);
                     }
                }
            }
        }
    }

    /**
     * sets defaults defined in the data model
     */
    public function defaults($fields = NULL, &$pointer = NULL)
    {
        //get default field set if none was sent
          if ($fields === NULL)
        {
                $fields = $this->get_fields();
        }
          //use object as pointer if no pointer was sent
          if ($pointer === NULL)
        {
                $pointer = $this;
          }
          //loop through each defined field
          foreach ($fields as $field_class => $Field)
        {
            //if this is a relationship reference
            if (is_array($Field) && isset($Field['_id']) && isset($Field['model']) && count($Field) === 2)
            {
                //load the field
                $Field = new $Field['_id']();
            }
            $field_key = $Field->name;
                //if a default was assigned to this data point and we are not setting a null value
                if (($Field->defaultvalue() !== NULL && $Field->nullvalue() === FALSE) || ($Field->defaultvalue() === NULL && $Field->nullvalue() === TRUE))
            {
                //if this value is not already set
                if ((is_array($pointer) && !isset($pointer[$field_key])) || (is_object($pointer) && !isset($pointer->$field_key)))
                {
                     if (is_array($pointer))
                    {
                         $pointer[$field_key] = $Field->defaultvalue();
                     }
                    else if (is_object($pointer))
                    {
                         $pointer->$field_key = $Field->defaultvalue();
                     }
                }
            }
            //if this field is an object or a set of objects, loop through each sub-object to look for defaults to set
            if (($Field->storage == 'keyed_array' || $Field->datatype == 'object') && isset($Field->submodel) && is_array($Field->submodel))
            {
                $set_sub_defaults = FALSE;
                if (is_array($pointer) && isset($pointer[$field_key]))
                {
                    $send_pointer = &$pointer[$field_key];
                    $set_sub_defaults = TRUE;
                }
                else if (is_object($pointer) && isset($pointer->$field_key))
                {
                    $send_pointer = &$pointer->$field_key;
                    $set_sub_defaults = TRUE;
                }
                //recursivly run this method to set defaults on any sub-object field values
                if ($set_sub_defaults)
                {
                    //an array of objects or a keyed array of objects
                    if (($Field->storage == 'keyed_array' || $Field->storage == 'array') && $Field->datatype == 'object')
                    {
                         $object_set = $send_pointer;
                         foreach ($object_set as $object_set_key => $object)
                        {
                            $this_send_pointer = &$send_pointer[$object_set_key];
                             $this->defaults($Field->fields,$this_send_pointer);
                         }
                    }
                    //a single object
                    else if ($Field->storage == 'keyed_array')
                    {
                         $this->defaults($Field->fields,$send_pointer);
                    }
                }
                }
          }
    }

    /**
     *
     */
    public function validate($data = NULL, $fields = NULL, $self = FALSE)
    {
        $messages = array();
        $validate_result = TRUE;
        //get all fields for this object
        if ($fields === NULL)
        {
            $fields = $this->get_fields();
        }
        //get pk field key
        $pk = $this->cfg('pk_name');
        //get the primary driver incase we need to search to make sure this is unique
        $drivers = $this->cfg('drivers');
        $Driver = $drivers[0];
        if ($data === NULL)
        {
            $data = $this->to_array();
        }
        $params = array('this'=> $this, 'fields' => &$fields, 'driver'=> &$Driver, 'data'=> &$data, 'self'=> &$self);
        $this->model_event('validate_start',$params);
         //loop through all fields and validate
        foreach ($fields as $Field)
        {
            //get field key
            $field_key = $Field->name;
            //if this field is the pk, and we are not loaded from db,
            if ($Field->pk && !isset($data[$field_key]) || $field_key == $this->cfg('pk_name'))
            {
                //skip validating pk
                continue;
            }
            //if value is set on this object, get it
            if (isset($data[$field_key]) || array_key_exists($field_key,$data))
            {
                $value = $data[$field_key];
            }
            //value is not set
            else
            {
                $value = $Field::NOT_SET;
            }
            //validate the value
            $Result = $Field->validate($value);
            //if the value failed validation
            if ($Result->ok() === FALSE)
            {
                $messages[$field_key] = $Result->message();
                $validate_result = FALSE;
            }
            //check unique
            if ($Field->unique !== FALSE && isset($data[$field_key]))
            {
                if ($Field->unique === TRUE)
                {
                    //setup query where
                    $where = array($field_key => $data[$field_key]);
                }
                //if unique is set as an array, then this is a combined unique field
                else if (is_array($Field->unique))
                {
                    $where = array();
                    foreach ($Field->unique as $unique_field_key)
                    {
                        $where[$unique_field_key] = $data[$unique_field_key];
                    }
                }
                //only select the pk from the db
                $query_fields = array($pk);
                //@todo change this to use a db $ne operator for the current pk once that feature is added to the db class and drivers
                //query the primary db
                $result = static::query(array(
                    'where'=> $where,
                    'columns'=> $query_fields,
                    'limit'=> 1,
                    'allowed' => TRUE,
                ));
                //if we found a record
                if ($result && isset($data[$pk]))
                {
                    //if there is no pk or there is a pk but it does not match the returned row
                    if (!isset($result->$pk) || (isset($result->$pk) && (string) $result->$pk !== (string) $data[$pk]))
                    {
                        $messages[$field_key] = $Field->message('unique');
                        $validate_result = FALSE;
                    }
                }
            }
        }
        $params = array('this'=> $this, 'fields' => &$fields, 'driver'=> &$Driver, 'data'=> &$data, 'result'=> &$validate_result, 'messages'=> &$messages, 'self' => &$self);
        $this->model_event('validate_end',$params);
        //@todo return status data with messages for each data key so it can be used to populate form error messages by field
        //validation passed
        if ($validate_result)
        {
            return new Status(TRUE,$messages);
        }
        //validation failed
        else
        {
            return new Status(FALSE,$messages,$messages);
        }
    }

    /**
     * returns updates discovered on the object from running 'changes()' OR adds a key to the updated config, used to call events when something is updated
     * @param string $key key of field
     * @param mixed $new_value
     * @param mixed $old_value
     */
    public function updated($key = NULL,$new_value = NULL, $old_value = NULL)
    {
        //if no key was sent, return all updates
        if ($key === NULL)
        {
            return isset($this->__cfg['updated']) ? $this->__cfg['updated'] : NULL;
        }
        else
        {
            $change = array('new_value'=> $new_value,'old_value'=> $old_value);
            $updated = $this->cfg('updated');
            if (!isset($updated[$key]))
            {
                $updated[$key] = $this->__cfg['updated'][$key] = array();
            }
            if (!is_array($updated[$key]) || !in_array($change,$updated[$key]))
            {
                $this->__cfg['updated'][$key][] = $change;
            }
        }
    }

    /**
     * returns adds discovered on the object from running 'changes()' OR adds a key to the added config, used to call events when something is added
     * @param string $key key of field
     * @param mixed $new_value
     */
    public function added($key = NULL, $new_value = NULL)
    {
        if ($key === NULL)
        {
            return isset($this->__cfg['added']) ? $this->__cfg['added'] : NULL;
        }
        else
        {
            $added = $this->cfg('added');
            if (!isset($added[$key]))
            {
                $added[$key] = $this->__cfg['added'][$key] = array();
            }
            if (!is_array($added[$key]) || !in_array($new_value,$added[$key])) {
                $this->__cfg['added'][$key][] = $new_value;
            }
        }
    }

    /**
     * returns removes discovered on the object from running 'changes()' OR adds a key to the removed config, used to call events when something is removed
     * @param string $key key of field
     * @param mixed $old_value
     */
    public function removed($key = NULL, $old_value = NULL)
    {
        if ($key === NULL)
        {
            return isset($this->__cfg['removed']) ? $this->__cfg['removed'] : NULL;
        }
        else
        {
            $removed = $this->cfg('removed');
            if (!isset($removed[$key]))
            {
                $removed[$key] = $this->__cfg['removed'][$key] = array();
            }
            if (!is_array($removed[$key]) || !in_array($old_value,$removed[$key]))
            {
                $this->__cfg['removed'][$key][] = $old_value;
            }
        }
    }

    /**
     * returns changes discovered on the object from running 'changes()' OR adds a key to the changed config, used to call events when something is changed (updated, added, or removed)
     * @param string $key key of field
     * @param string $type indicates if this is an 'added', 'updated', or 'removed'
     * @param mixed $new_value if type=added or type=updated, this contains the new value
     * @param mixed $old_value  if type=updated or type=removed, this contains the old value
     */
    public function changed($key = NULL, $type = NULL, $new_value = NULL, $old_value = NULL)
    {
        //if no key was sent, return all updates
        if ($key === NULL)
        {
            return isset($this->__cfg['changed']) ? $this->__cfg['changed'] : NULL;
        }
        else
        {
            $change = array('type'=> $type, 'new_value'=> $new_value,'old_value'=> $old_value);
            $changed = $this->cfg('changed');
            if (!isset($changed[$key]))
            {
                $changed[$key] = $this->__cfg['changed'][$key] = array();
            }
            if (!is_array($changed[$key]) || !in_array($change,$changed[$key])) {
                $this->__cfg['changed'][$key][] = $change;
            }
        }
    }

    /**
     * @returns array all public columns and fields (skips hidden/cfg properties)
     */
    public function to_array($check_access = TRUE,$display = FALSE)
    {
        //set all defaults on object
        $this->defaults();
        //filter all values
        $this->filter();
        $fields = $this->get_fields();
        $cols = array();
        //loop through all set values
        foreach ($this as $col => $val)
        {
            //skip hidden properties (ones that start with '_')
            if (substr($col,0,2) == '__' && $col != $this->cfg('trait_column')) continue;
            //only export valid fields
            if (!isset($fields[$col]))
            {
                continue;
            }
            //if we want to check access before returning the data
            if ($check_access)
            {
                 //only users with admin access can see private fields.
                 if ($fields[$col]->private === TRUE && !in_array('admin',$this->get_user_access_tags()))
                 {
                     continue;
                 }
            }
            //ugly hack in place until we code for a "owner private" field access type that is update-able by the owner, but is never shown or returned
            if ($display === TRUE && $col == 'password')
            {
             continue;
            }
            //store values in array
            if ($val InstanceOf DateTime)
            {
                $val = $val->getTimestamp();
            }
            $cols[$col] = $val;
        }
        //return array of values
        return $cols;
    }

    public function to_display($check_access = TRUE)
    {

    }

    public function to_storage($check_access = TRUE)
    {

        //set all defaults on object
        $this->defaults();
        //filter all values
        $this->filter();

        // Get field objects
        $fields = $this->get_fields();

        // init return array
        $cols = array();

        //loop through all set values
        foreach ($this as $col => $val)
        {
            //skip hidden properties (ones that start with '_')
            if (substr($col,0,2) == '__' && $col != $this->cfg('trait_column')) continue;

            //only export valid fields
            if (!isset($fields[$col]))
            {
                continue;
            }

            //if we want to check access before returning the data
            if ($check_access)
            {
                 //only users with admin access can see private fields.
                 if ($fields[$col]->private === TRUE && !in_array('admin',$this->get_user_access_tags()))
                 {
                     continue;
                 }
            }

            // Skip any non-stored fields
            if ($fields[$col]->stored === FALSE)
            {
                continue;
            }

            //store values in array
            if ($val InstanceOf DateTime)
            {
                $val = $val->getTimestamp();
            }
            $cols[$col] = $val;
        }

        //return array of values
        return $cols;
    }

    /**
     * removes any values that are empty or null and that have 'not_empty' defined in their definition
     */
    public function clean_loaded_data($field_set = NULL, &$path = NULL)
    {
        if (is_null($field_set)) $field_set = $this->cfg('fields');
        if (is_null($path)) $path = &$this;
        //loop through all defined fields
        foreach ($field_set as $field_key => $field) {
            //skip fields that don't have 'not_empty' set
            if ($field['not_empty'] === TRUE) {
                if (is_object($path)) {
                    if (isset($path->$field_key) && (is_null($path->$field_key) || $path->$field_key === '')) {
                        unset($path->$field_key);
                    }
                } else if (is_array($path)) {
                    if (isset($path[$field_key]) && (is_null($path[$field_key]) || $path[$field_key] === '')) {
                        unset($path[$field_key]);
                    }
                }
            }
            if ($field['type'] == 'object' || $field['type'] == 'object_set') {
                if (is_object($path)) {
                    if (isset($path->$field_key)) {
                        $this->clean_loaded_data($field['fields'],$path->$field_key);
                    }
                } else if (is_array($path)) {
                    if (isset($path[$field_key])) {
                        $this->clean_loaded_data($field['fields'],$path[$field_key]);
                    }
                }
            }
        }
    }

    /**
     * sets public properies for each data point exactly as it is received from the db
     * @param array $data
     */
    public function load($data)
    {
        //loop through each field key / value
        foreach ($data as $field_key => $val)
        {
            //assign column value to object by column key
            $this->set($field_key,$val);
        }
    }

    /**
     * takes data from _POST with field__ prefixes to data and extracts just the data meant to be loaded into the model
     */
    public static function extract_post_data($post)
    {
        $data = array();
        $class = get_called_class();
          $model = new $class();
        $model_name = $model->cfg('name');
        //@todo check model permissions for create/update permission
        $fields = $class::get_fields();
        //loop through all posted keys
        foreach ($post as $key => $value)
        {
            //if this key is a field meant to be stored on the model
            if (stripos($key,'field__') === 0)
            {
                $set_value = TRUE;
                $field_key = strtolower(str_ireplace('field__','',$key));
                $field_class = 'field_'.$model_name.'_'.ucfirst($field_key);
                //ensure this field class exists
                if (!class_exists($field_class) || !isset($fields[$field_key]))
                {
                    continue;
                }
                //@todo check field permissions for create/update/delete
                $Field = new $field_class();
                if (isset($post['checkbox__'.$field_key]) && $post['checkbox__'.$field_key] == 'true' && $value === 'on')
                {
                    if ($Field->datatype === 'boolean')
                    {
                        $value = TRUE;
                    }
                    else
                    {
                        $value = 1;
                    }
                }
                if ($Field->datatype === 'boolean')
                {
                    $value = (bool) $value;
                }
                if ($Field->datatype === 'int')
                {
                    $value = (int) $value;
                }
                if ($Field->datatype === 'float')
                {
                    $value = (float) $value;
                }
                if ($Field->datatype === 'string')
                {
                    //@todo cast at proper charset
                    $value = (string) $value;
                }
                if ($Field->datatype === 'datatime')
                {
                    if ($value === '')
                    {
                        $set_value = FALSE;
                    }
                }
                if ($Field->datatype === 'timestamp')
                {
                    if ($value === '')
                    {
                        $set_value = FALSE;
                    }
                    else
                    {
                        $value = (int) $value;
                    }
                }
                if (($Field->storage !== 'single' || $Field->datatype === 'object') && $value === "")
                {
                    $set_value = FALSE;
                }
                //if the data is expected in json format (for complex fields), decode it to a php object
                if ($Field->storage === 'array' || $Field->storage === 'keyed_array' || $Field->datatype === 'array' || $Field->datatype === 'object' || $Field->datatype === 'relationship' || $Field->datatype === 'resource')
                {
                    $value = json_decode($value,TRUE);
                    if ($value === NULL)
                    {
                        $set_value = FALSE;
                    }
                }
                if ($set_value)
                {
                    $data[$field_key] = $value;
                }
            }
            //catch for checkboxes that are unchecked and aren't sent
            else if (stripos($key,'checkbox__') === 0)
            {
                $field_key = strtolower(str_ireplace('checkbox__','',$key));
                //if a checkbox exists for this field, but no post value exists for it, assume 'off'
                if (!isset($post['field__'.$field_key]))
                {
                    $field_class = 'field__'.$model_name.'__'.ucfirst($field_key);
                    //ensure this field class exists
                    if (!class_exists($field_class) || !isset($fields[$field_key]))
                    {
                        continue;
                    }
                    //@todo check field permissions for create/update/delete
                    $Field = new $field_class();
                    if ($Field->datatype === 'boolean')
                    {
                        $value = FALSE;
                    }
                    else
                    {
                        $value = 0;
                    }
                    $data[$field_key] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * framework or app level function that can return a primary key value to be used in all databases.
     */
    public function get_new_pk()
    {
        $pk = $this->get_framework()->get_new_pk($this);
        $this->model_event('get_new_pk',array('pk'=> &$pk));
        return $pk;
    }

    /**
     * save current object state to the database(s)
     */
    public function save()
    {
        // prepare data for storage
        $set = $this->to_storage();

        $messages = array();
        $saves_result = TRUE;

        //get pk field key
        $pk = $this->cfg('pk_name');

        //get db drivers
        $drivers = $this->cfg('drivers');
        
        //detect if this is an insert or an update.  if a pk is set but we didn't load the object from the db.
        if (isset($this->$pk) && ($this->loaded() === NULL || $this->loaded() === FALSE))
        {
            //search primary db for this pk
            $exists = static::query(array(
                'where'=> array($pk => $this->$pk),
                'limit'=> 1,
                'allowed' => TRUE,
            ));
            //if this entry is already in the db
            if ($exists)
            {
                //this is an update
                $is_insert = FALSE;
            }
            //if this entry is not in the db
            else
            {
                //this is an insert
                $is_insert = TRUE;
            }
        }
        //if no pk is set, assume insert
        else if (!isset($this->$pk))
        {
            $is_insert = TRUE;
        }
        
        //if a pk is set
        else
        {
                $is_insert = FALSE;
        }

        $action = ($is_insert) ? 'create' : 'update';

        //ensure bound user has permission
        if ( ! $this->allowed($action,$this))
        {
            //@todo finish adding access controls for all actions and add a user
            throw new Supermodlr_Exception('Not Authorized', 401);
        }        

        $params = array('this'=> $this, 'drivers'=> &$drivers, 'is_insert'=> &$is_insert, 'set'=> &$set, 'result', &$saves_result, 'messages'=> &$messages);
        
        $this->model_event('save',$params);

        //validate this object before it is saved
        $valid = $this->validate($set);
        if ($valid->ok() === FALSE) {
            return $valid;
        }

        //loop through all drivers (primary first)
        foreach ($drivers as $Driver) {
            //if this db supports transactions
            if ($Driver->supports_transactions())
            {
                //detect external transaction
                if ($Driver->in_transaction())
                {
                    $internal_transaction = FALSE;
                }
                else
                {
                    $internal_transaction = TRUE;
                }
            }
            else
            {
                $internal_transaction = FALSE;
            }
             //start transaction
             if ($internal_transaction)
             {
                $Driver->start_transaction();
            }
             //if this is a loaded existing entry, then update
             if (!$is_insert) {
                $action = 'update';
                //setup update array
                $update = array(
                    'into'=> $this->cfg('db_name'),
                    'set'=> $set,
                    'where'=> array($pk => $this->$pk),
                    'model'=> $this,
                    'fields'=> $this->get_fields(),
                );
                //call events to modify update
                $this->model_event('save_update',$update);
                //run update
                $result = $Driver->update($update);
            //if this was a newly created blank object, then insert
            } else {
                $action = 'create';
                $new_pk = $this->get_new_pk();
                //if the framework or app returned a new pk to use
                if ($new_pk !== NULL)
                {
                    $set[$pk] = $new_pk;
                }
                //if no new pk was returned, expect the primary driver to create an return one
                else
                {
                    if (isset($set[$pk]))
                    {
                        unset($set[$pk]);
                    }
                }
                //setup insert array
                $create = array(
                    'into'=> $this->cfg('db_name'),
                    'set'=> $set,
                    'model'=> $this,
                    'fields'=> $this->get_fields(),
                );
                //call events to modify create array
                $this->model_event('save_create',$create);
                //run create
                $result = $Driver->create($create);
            }
            //if result is ok && either no transaction or ok transaction
            if ($result !== FALSE && (!$internal_transaction || $Driver->transaction_status()))
            {
                if ($is_insert)
                {
                    //set the primary key
                    $this->$pk = $result;
                }
                //commit transaction
                if ($internal_transaction)
                {
                    $Driver->end_transaction();
                }
            }
            //insert/update failed for this driver
            else
            {
                $saves_result = FALSE;
                if (is_object($Driver->error()))
                {
                    $messages[] = $Driver->error()->getMessage();
                }
                if ($internal_transaction)
                {
                    $Driver->rollback_transaction();
                }
                //@todo need to add logic here
                //if this is not primary db, and primary db save worked, somehow log this to to re-run on secondary/alternate db
                //break out of driver loop
                break;
            }
        }
        //all save methods were successfull
        if ($saves_result)
        {
            //@todo clear all related cache
            if (!$is_insert)
            {
                $this->clear_cache();
            }
            //@todo generate cache
            //detect changes
            $changes = $this->changes();
            //execute all events for changed data
            $updated = $changes['updated'];
            if (is_array($updated))
            {
                foreach ($updated as $key => $updated_set)
                {
                     $event_key = $key.'__updated';
                     $args = array($this->cfg('name')=> $this,'data'=> $updated_set);
                     $this->model_event($event_key,$args);
                    //if this key contains a reference to an indexed array
                    if (preg_match("/[.][0-9]+([.]|$)/",$key))
                    {
                        $new_key = preg_replace("/[.][0-9]+([.]|$)/",".i\1",$key);
                        $event_key = $new_key.'__updated';
                        $args = array($this->cfg('name')=> $this,'data'=> $updated_set);
                        $this->model_event($event_key,$args);
                    }
                }
            }
            $added = $changes['added'];
            if (is_array($added))
            {
                foreach ($added as $key => $added_set)
                {
                     $event_key = $key.'__added';
                     $args = array($this->cfg('name')=> $this,'data'=> $added_set);
                     $this->model_event($event_key,$args);
                    //if this key contains a reference to an indexed array
                    if (preg_match("/[.][0-9]+([.]|$)/",$key))
                    {
                        $new_key = preg_replace("/[.][0-9]+([.]|$)/",".i\1",$key);
                        $event_key = $new_key.'__added';
                        $args = array($this->cfg('name')=> $this,'data'=> $added_set);
                        $this->model_event($event_key,$args);
                    }
                }
            }
            $removed = $changes['removed'];
            if (is_array($removed))
            {
                foreach ($removed as $key => $removed_set)
                {
                     $event_key = $key.'__removed';
                     $args = array($this->cfg('name')=> $this,'data'=> $removed_set);
                     $this->model_event($event_key,$args);
                    //if this key contains a reference to an indexed array
                    if (preg_match("/[.][0-9]+([.]|$)/",$key))
                    {
                        $new_key = preg_replace("/[.][0-9]+([.]|$)/",".i\1",$key);
                        $event_key = $new_key.'__removed';
                        $args = array($this->cfg('name')=> $this,'data'=> $removed_set);
                        $this->model_event($event_key,$args);
                    }
                }
            }
            //changed (aggregate of updated, added, removed)
            $changed = $changes['changed'];
            if (is_array($changed))
            {
                foreach ($changed as $key => $changed_set)
                {
                    $event_key = $key.'__changed';
                    $args = array($this->cfg('name')=> $this,'data'=> $changed_set);
                    $this->model_event($event_key,$args);
                    //if this key contains a reference to an indexed array
                    if (preg_match("/[.][0-9]+([.]|$)/",$key))
                    {
                        $new_key = preg_replace("/[.][0-9]+([.]|$)/",".i\1",$key);
                        $event_key = $new_key.'__changed';
                        $args = array($this->cfg('name')=> $this,'data'=> $changed_set);
                        $this->model_event($event_key,$args);
                    }
                }
            }
            // re-set loaded data to the current value
            $this->cfg('loaded_data',$this->to_array());
            // set loaded to true so that any other save operations are an update instead of an insert
            $this->cfg('loaded',TRUE);
            //call events to run after update/insert
            $params = array('this'=> $this, 'is_insert'=> $is_insert, 'result'=> $saves_result, '_id'=> $this->$pk, 'messages'=> $messages);
            $this->model_event('save_end',$params);
            if ($is_insert)
            {
                $this->model_event('object_created',$params);
            }
            else
            {
                $this->model_event('object_updated',$params);
            }
            return new Status(TRUE, $messages, array('_id'=> $this->$pk));
        }
        //data save failed, generate fatal error
        else
        {
            $message = 'There was an error storing this object.';
            $this->error($messages);
            return new Status(FALSE,$messages);
        }
    }
    /**
     * clears cache of an object
      */
    public function clear_cache()
    {
        $args = array();
        $this->model_event('clear_cache',$args);
    }
    /**
     * sets a column value
      */
    public function set($key,$value)
    {
        $params = array('key'=> &$key,'value'=> &$value);
        $this->model_event('set',$params);
        $fields = $this->get_fields();
        $model_name = $this->get_name();

        $Field = NULL;

        //if this is a sub key (supports 'key1.key2[.key3...]' format
        if (strpos($key,'.') !== FALSE)
        {
            //put keys in an array
            $keys = explode('.',$key);

            //get first key
            $first = $keys[0];

            //create first key if it doesn't exist
            if (!isset($this->$first))
            {
                $this->$first = array();
            }

            //set initial position as a reference
            $position = &$this->$first;

            //remove first key from list of keys
            unset($keys[0]);

            //loop through each key as assign the new position as a reference
            foreach ($keys as $ikey)
            {
                $position = &$position[$ikey];
            }

            //assign the value to the position value
            $position = $value;

            //@todo fix for sub-fields/sub models
            $Field = NULL;
        }
        else
        {
            if (isset($fields[$key]))
            {
                $Field = $fields[$key];
                if (!isset($this->$key))
                {
                    $this->$key = NULL;
                }                
                $position = &$this->$key;                
            }
            
        }
        $set_value = TRUE;

        //ensure this field class exists
        if (!($Field InstanceOf Field))
        {
            //@todo throw error, cannot set a property if it is not part of a field assigned to this model
            $this->cfg('meta.'.$key,$value);
            return ;
        }

        //@todo check field permissions for create/update/delete
        if ($value === NULL)
        {
            //if null is not a valid value
            if ($Field->nullvalue !== TRUE)
            {
                $set_value = FALSE;
            }
            else
            {
                $casted_value = NULL;
            }
        }
        else if (($Field->storage !== 'single' || $Field->datatype === 'object') && !is_array($value))
        {
            $set_value = FALSE;
        }
        else
        {
            if ($Field->storage === 'single')
            {
                $values = array($value);
            }
            else
            {
                $values = $value;
            }
            $casted_values = array();
            foreach ($values as $k => $value)
            {
                if ($Field->datatype === 'boolean')
                {
                    $casted_values[$k] = (bool) $value;
                }
                else if ($Field->datatype === 'int')
                {
                    $casted_values[$k] = (int) $value;
                }
                else if ($Field->datatype === 'float')
                {
                    $casted_values[$k] = (float) $value;
                }
                else if ($Field->datatype === 'string')
                {
                    //@todo cast at proper charset
                    $casted_values[$k] = (string) $value;
                }
                else if ($Field->datatype === 'datetime')
                {
                    if ($value === '' || (is_string($value) && !strtotime($value)))
                    {
                        $set_value = FALSE;
                    }
                    else
                    {
                        $casted_values[$k] = $value;
                    }
                }
                else if ($Field->datatype === 'timestamp')
                {
                    if ($value === '' || !is_numeric($value))
                    {
                        $set_value = FALSE;
                    }
                    else
                    {
                        $casted_values[$k] = (int) $value;
                    }
                }
                //@todo relationship cast
                //@todo binary cast
                //uncastable datatype
                else
                {
                    $casted_values[$k] = $value;
                }
            }
            if ($Field->storage === 'single')
            {
                //get value back out of
                $casted_value = reset($casted_values);
            }
            else
            {
                $casted_value = $casted_values;
            }
        }
        //if we want to set this value on the model
        if ($set_value)
        {
            $position = $casted_value;
        }
        $this->model_event('set_end',$params);
    }

    /**
     * removes a column value
     * @param string $key
     */
    public function remove($key) {
        $params = array('key'=> $key);
        $this->model_event('remove',$params);
        foreach ($params as $var => $val) $$var = $val;
        //if this is a sub key (supports 'key1.key2[.key3...]' format
        if (strpos($key,'.') !== FALSE)
        {
            //put keys in an array
            $keys = explode('.',$key);
            //get first key
            $first = $keys[0];
            //create first key if it doesn't exist
            if (!isset($this->$first))
            {
                $this->$first = array();
            }
            //set initial position as a reference
            $position = &$this->$first;
            //remove first key from list of keys
            unset($keys[0]);
            //loop through each key as assign the new position as a reference
            $c = 0;
            foreach ($keys as $ikey)
            {
                //if this is not the last element, reset the reference pointer
                if (count($keys)-1 < $c)
                {
                    $position = &$position[$ikey];
                }
                //if this is the last element, unset the value
                else
                {
                    unset($position[$ikey]);
                }
                $c++;
            }
        }
        else
        {
            unset($this->$key);
        }
        $this->model_event('remove_end',$params);
    }

    //delete this object for all db's
    public function delete($params = array())
    {

        //ensure bound user has permission
        if ( ! $this->allowed('delete',$this))
        {
            throw new Supermodlr_Exception('Not Authorized', 401);
        }  

        //get db drivers
        $drivers = $this->cfg('drivers');
        //get pk field key from obj cfg
        $pk = $this->cfg('pk_name');
        //ensure pk is set on this object so we can delete by that id
        if (!isset($this->$pk))
        {
            return new Status(FALSE,'Cannot delete this object because there is no id');
        }
        //setup remove where array
        $where = array($pk => $this->$pk);
        $deleted = TRUE;
        $messages = array();
        //loop through all drivers
        foreach ($drivers as $i => $Driver)
        {
            //remove this entry from this db
            $delete_result = $Driver->delete(array(
                'from'=> $this->cfg('db_name'),
                'where'=> $where,
                'model'=> $this,
                'fields'=> $this->get_fields(),
            ));
            //if returned affected rows is not one and remove result is not true (for drivers that don't support 'affected rows')
            if ($delete_result !== 1 && $delete_result !== TRUE)
            {
                //if this is the primary db, fail delete result
                if ($i === 0)
                {
                    $deleted = FALSE;
                    $Error = $Driver->error();
                    if (is_object($Error))
                    {
                        $messages[] = $Error->getMessage();
                    }
                    break;
                }
                //if delete failed on a non-primary db, log the operation failure and schedule it to be removed
                else
                {
                    //@todo throw warning and schedule this to be removed from the non-primary db
                    $messages[] = 'Unable to delete model '.$this->cfg('name').' from driver '.$i.' ('.get_class($Driver).') where '.var_export($where,TRUE);
                }
            }
        }
        $removed_id = $this->$pk;
        //run changes on a mock empty data set and run all 'removed' events
        if ($deleted)
        {
            //detect all removed fields
            $empty_data_set = array();
            //run changes, sending an empty data set
            $changes = $this->changes($empty_data_set);
            //run all events for removed fields
            $removed = $changes['removed'];
            if (is_array($removed))
            {
                foreach ($removed as $key => $removed_set)
                {
                     $event_key = $key.'__removed';
                     $args = array($this->cfg('name')=> $this,'data'=> $removed_set);
                     $this->model_event($event_key,$args);
                    //if this key contains a reference to an indexed array
                    if (preg_match("/[.][0-9]+([.]|$)/",$key))
                    {
                        $new_key = preg_replace("/[.][0-9]+([.]|$)/",".i\1",$key);
                        $event_key = $new_key.'__removed';
                        $args = array($this->cfg('name')=> $this,'data'=> $removed_set);
                        $this->model_event($event_key,$args);
                    }
                }
            }
        }
        $params = array('this'=> $this, 'result'=> $deleted, '_id'=> $removed_id, 'messages'=> $messages);
        $this->model_event('delete_end',$params);
        return new Status($deleted,$messages);
    }

    /**
     * looks for event methods in traits and in the class tree and calls them
     * 
     * this method looks for any functions on any class in the class tree related traits related to $key and runs them.
     * event naming format: event__{$class_name}__{$key} - this allows us to bind any number of events for any number of object extensions and trait inclusions
     * @param string $key required
     * @param array $args = array() - parameters here are usually passed by reference so that the events can modify the sent args
     */
    public function model_event($key,$args=array())
    {
        $key = str_replace('.','_',$key);
        $key = preg_replace("/[^0-9a-z_]/i","",$key);
        $class_tree = $this->get_class_tree();
        foreach ($class_tree as $class)
        {
            $event_method = strtolower('event__'.$class.'__'.$key);
            if (method_exists($this,$event_method))
            {
                $this->$event_method($args);
            }
        }
    }

    //allow datatypes to run code on event keys
    public static function static_model_event($key,$args = array()) {
        $called_class = get_called_class();
        //get model data type name
        $class = $called_class::get_model_class();
        //$trait_events = $called_class::get_trait_events($key);
        //run all trait events
        /*foreach ($trait_events[$key] as $trait => $methods) {
            foreach ($methods as $method) {
                $trait::$method($args);
            }
        }*/
        //run method on data-type class
        $class_method = $class.'_'.$key;
        if (method_exists($class,$class_method)) {
            $class::$class_method($args);
        }
    }

    public function error($message = NULL)
    {
        if ($message === NULL)
        {
            return $this->cfg('error');
        }
        else
        {
            $this->cfg('error',$message);
        }
    }

    /**
     * decides if an action or set of actions is allowed on this model given the bound user (or anon) context
     * 
     * @param mixed $actions a string or an array of strings to check for permissions against this model and the bound user.
     *
     * @access public
     * @static
     *
     * @return bool True if all the sent actions are allowed, False if non of the sent actions are allowed.
     */
    public static function allowed($actions,$context_object = NULL)
    {
        // If cfg is set to always allow
        if (static::scfg('always_allowed') === TRUE)
        {
            // Bypass security.  This should be used very carefully since it bypasses all model and field security
            return TRUE;
        }

        // Get all user access tags for the current bound user (if any)
        $tags = static::get_user_access_tags();

        // If this is the admin
        if (in_array('admin', $tags))
        {
            // Admins can do anything
            return TRUE;
        }

        // Get owner field key (if any)
        $owner_field_key = static::owner_field();

        // If there is an owner field and $context_object was sent and the owner_field_key has a value on this object
        if ($owner_field_key && $context_object !== NULL && isset($context_object->$owner_field_key) && $context_object->$owner_field_key !== NULL)
        {
            // Get bound user
            $User = $context_object->get_bound_user();

            // Get the primary key field name for the user object
            $pk = $User->cfg('pk_name');

            // Expect the related user to be related via a relationship field.  @todo decide if we should allow other field types to store an "owner" field.
            $owner_value = $context_object->$owner_field_key;

            //if this field value is an array, assume relationship (for now)
            if (is_array($owner_value) && isset($owner_value['_id']))
            {
                $owner = (((string) $User->$pk) === ((string) $owner_value['_id']));
            }
            //assume raw value
            else
            {
                $owner = (((string) $User->$pk) === ((string) $owner_value));
            }

            // If a user was found and the id's exactly match
            if ($User && $owner)
            {
                // Add in the owner tag
                $tags[] = 'owner';
            }
        }

        // If a single action was sent
        if (!is_array($actions))
        {
            // Turn it into an array
            $actions = array($actions);
        }

        // Get this models access rules
        $model_access_rules = static::get_model_access_rules();

        // Loop through all sent actions
        foreach ($actions as $action)
        {
            $allowed = FALSE;

            // Loop through all user roles
            foreach ($tags as $tag)
            {
                // If this user role is valid for this action
                if (in_array($tag, $model_access_rules[$action]))
                {
                    // Set allowed to true for this action
                    $allowed = TRUE;

                    // Continue to check the next action
                    break ;
                }
            }

            // If this action was not allowed, break out of all and return FALSE
            if ( ! $allowed)
            {
                break;
            }
                
        }
        return $allowed;
    }

    /**
     * query a model.  returns a set of objects or a single object if limit=1 is sent 
     * send array('where'=> array('column.name'=> 'value')) as $params
     * you can also send any other params that the model::$db driver supports
     * if array('count'=> TRUE) is sent in params, an int is returned instead of a result set of objects
     */
    public static function query($params) 
    {
        //ensure bound user has read permission
        if ( (isset($params['allowed']) && $params['allowed'] !== TRUE) || static::scfg('external_api') === TRUE)
        {
            if ( ! static::allowed('read'))
            {
                //@todo finish adding access controls for all actions and add a user
                throw new Supermodlr_Exception('Not Authorized', 401);                
            }
        }
        $class = get_called_class();
        $o = new $class();
        $drivers = $o->cfg('drivers');
        $pk = $o->cfg('pk_name');
        //default to primary db
        if (!isset($params['driver'])) $params['driver'] = 0;
        if (!isset($params['count'])) $params['count'] = FALSE;
        if (!isset($params['limit'])) $params['limit'] = NULL;
        if (!isset($params['from'])) $params['from'] = $o->cfg('db_name');
        if (!isset($params['array'])) $params['array'] = FALSE;
        if (!isset($params['fields'])) $params['fields'] = $class::get_fields();
        if (!isset($params['model'])) $params['model'] = $o;
        //if (!isset($params['cache'])) $params['cache'] = $o->cfg('read_cache');
        //get driver @todo add support to select driver by driver.id stored in driver_config
        $Driver = $drivers[$params['driver']];
        $results = $Driver->read($params);
        $result_set = array();
        if ($results)
        {
            if ($params['count'] !== TRUE)
            {
                $count = 0;
                foreach ($results as $k => $row)
                {
                    if ($params['array'] === TRUE)
                    {
                        $result_set[] = $row;
                    }
                    else
                    {
                        $result_set[] = new $class($row[$pk], $row);
                    }
                }
                if ($params['limit'] == 1)
                {
                    $result_set = reset($result_set);
                }
            }
            else
            {
                $result_row = reset($results);
                $result_set = intval($result_row['count']);
            }
        }
        return $result_set;
    }



    // @todo maybe all methods should be calculated at 'data-type' compile time and added as an array on the generated data type object model php class file
    public static function get_trait_events($key = NULL) {
        $called_class = get_called_class();
        //get model data type name
        $class = $called_class::get_model_class();
        $trait_event_key = 'trait_events_'.$class;
        //check if this event has already been run once
        $trait_events = $called_class::scfg($trait_event_key);
        //if we haven't searched for all methods
        if (is_null($trait_events) || !isset($trait_events[$key])) {
            if (is_null($trait_events)) {
                $trait_events = array();
            }
            $trait_events[$key] = array();
            //run methods on all traits
            $traits = $called_class::traits();
            foreach ($traits as $trait) {
                $trait_method = $trait.'_'.$key;
                if (method_exists($trait,$trait_method)) {
                    $trait_events[$key][$trait][] = $trait_method;
                }
                $class_trait_method = $class.'_'.$trait.'_'.$key;
                if (method_exists($class,$class_trait_method)) {
                    $trait_events[$key][$trait][] = $class_trait_method;
                }
            }
            $called_class::scfg($trait_event_key,$trait_events);
        }
    }

    public static function model_exists($model)
    {
        $model = ucfirst(strtolower($model));
        return (class_exists('Model_'.$model)) ? 'Model_'.$model : FALSE ;
    }

    public static function get_model_from_file($model_file)
    {
        //get file info
        $model_info = pathinfo($model_file);
        //build expected class name
        $model_class_name = 'Model_'.ucfirst(strtolower($model_info['filename']));
        return array('model_file'=> $model_file, 'model_class'=> $model_class_name, 'model_name'=> $model_info['filename']);
    }

    public static function get_models()
    {
        $called_class = get_called_class();
        if ($called_class::scfg('models') === NULL)
        {
            $models = array();
            //check module path
            if (is_dir(MODPATH.'supermodlr/classes/Model/'))
            {
                $model_dir = scandir(MODPATH.'supermodlr/classes/Model/');
                foreach ($model_dir as $model)
                {
                    //skip directories
                    if (substr($model, 0,1) === '.') continue;
                    $model_detail = $called_class::get_model_from_file($model);
                    $models[] = $model_detail;
                }
            }
            //check app path
            if (is_dir(APPPATH.'classes/Model/'))
            {
                $model_dir = scandir(APPPATH.'classes/Model/');
                foreach ($model_dir as $model)
                {
                    //skip directories
                    if (substr($model, 0,1) === '.') continue;
                    $model_detail = $called_class::get_model_from_file($model);
                    $models[] = $model_detail;
                }
            }
            $called_class::scfg('models',$models);
        }
        return $called_class::scfg('models');
    }
    
    /**
     * Resolves a relationship field by opening the model and retrieving the requested field value.  This is not efficient to use on a large number models (like a result set for example)
     * 
     * @param Supermodlr_Field $field     
     * @param string $rel_field Name of the field on the related model whose value you want to get
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function rel($field,$rel_field = NULL)
    {
        if (isset($this->$field) && is_array($this->$field) && !empty($this->$field))
        {
            //first check to see if this rel has been resolved
            if ($this->cfg('rel.'.$field) !== NULL)
            {
                $Rel = $this->cfg('rel.'.$field);
            }
            //resolve the relationship
            else
            {
                $rel = $this->$field;
                $rel_class = 'Model_'.ucfirst($rel['model']);
                $Rel = new $rel_class($rel['_id']);
            }
            //if no specific field was requested, return the entire rel object
            if ($rel_field === NULL)
            {
                return $Rel;
            }
            // return the requested field value
            else
            {
                return $Rel->$rel_field;
            }
        }
        return NULL;
    }

    /**
      * Gets the current bound user
      * 
      * If a user is not bound and the framework has not been checked yet, we check the framework for a user to bind for access calls
      * @return Model_Supermodlruser $User
      */
    public static function get_bound_user()
    {
        // If a user is not yet bound
        if (static::scfg('bound_user') === NULL)
        {
            // Load the framework
            $Framework = static::get_framework();

            // Ask the framework for a user, if any
            $User = $Framework->get_user();

            // If a valid user was returned
            if ($User InstanceOf Model_Supermodlruser)
            {
                // Bind the user
                static::bind_user($User);
            }
            // No valid user was returned
            else
            {
                // Set bound user to FALSE so we don't continue to check the framework for a user
                static::scfg('bound_user',FALSE);
            }
        }
        return static::scfg('bound_user');
    }

    /**
      * Binds a valid user to the static model class
      *
      * @param Model_Supermodlruser $User
      */
    public static function bind_user(Model_Supermodlruser $User)
    {
        static::scfg('bound_user',$User);
    }

    /**
      * Returns user access tags set on the bound user.  If there is not bound user, we return the default access tags (anon access)
      *
      * @return array $user_access_tags an array of tags that define this users current permissions.  By default, possible tags are array('admin','anon','auth')
      */
    public static function get_user_access_tags()
    {
        $User = static::get_bound_user();
        if ($User !== NULL && $User InstanceOf Model_Supermodlruser && isset($User->useraccesstags))
        {
                return $User->useraccesstags;
        }
        return static::scfg('user_access_tags');
    }

    /**
      * Returns an array of tags required by this model for various actions
      *
      */
    public static function get_model_access_rules()
    {
        $access = static::scfg('model_access_rules');
        $params['access_rules'] = &$access;
        static::static_model_event('get_model_access_rules',$params);
        return $access;
    }
}