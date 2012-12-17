<?php defined('SYSPATH') or die('No direct access allowed!');
 
class SupermodlrTest extends Kohana_UnitTest_TestCase
{
	public static $test_entry = array();

	//test class instantiation
	public function testInit() 
	{
		//get all models to be tested
        $models = Supermodlr::get_models();
 
		//loop through all model files
		foreach ($models as $model) 
		{
			//attempt to create the object, expect auto loading to load the file
			$model_obj = new $model['model_class']();
			
			//assert that the object was created
			$this->assertEquals(strtolower(get_class($model_obj)), strtolower($model['model_class']), 'Attempting to create model:'.$model['model_class']);
					
		}	
	}
	
	//test db driver connections
    public function testConnection()
    {
		//get all models to be tested
        $models = Supermodlr::get_models();
		foreach ($models as $model) 
		{	
			//attempt to create the object, expect auto loading to load the file
			$model_obj = new $model['model_class']();
			
			//get db drivers
			$drivers = $model_obj->cfg('drivers');
			
			//loop through all drivers
			foreach ($drivers as $Driver)
			{
				//connect to the db
				$connected = $Driver->connect();
				
				//assert that the driver connected ok
				$this->assertEquals($connected,TRUE,'Attempting to connect to driver '.get_class($Driver).' for model '.$model['model_class']);				
				
			}
		}
		
	}
	
	//test create
    public function testCreate()
    {
		//get all models to be tested
        $models = Supermodlr::get_models();
		foreach ($models as $model) 
		{
			//create a new blank object
			$model_obj = new $model['model_class']();
			
			//set test values
			$model_obj->set_test_valid_values();

			//save the model
			$save_result = $model_obj->save();

			//assert save result was TRUE
			$this->assertEquals($save_result->ok(), TRUE, 'Status Message: '.$save_result->message(). ' '.var_export($model_obj->error(),true));
			
			//get pk from db
			$pk = $model_obj->cfg('pk_name');
			
			//save this data so we can remove the test entry
			SupermodlrTest::$test_entry[$model['model_class']]['data'] = $model_obj->to_array();
			
			//save the pk name
			SupermodlrTest::$test_entry[$model['model_class']]['pk_name'] = $pk;
		}
		
    }
	
	//test defaults
    public function testDefaults()
    {
		//get all models to be tested
        $models = Supermodlr::get_models();
		foreach ($models as $model) 
		{
			//create a new blank object
			$model_obj = new $model['model_class']();
			
			//set all default values on the object
			$model_obj->defaults();
			
			//get values
			$values = $model_obj->to_array();
			
			//get object fields
			$fields = $model_obj->get_fields();
			
			//loop through all fields and look for default values that should have been set
			$this->check_defaults($fields,$values);
			
		}
    }	
	
	//recursive function to verify all default values were set
	public function check_defaults($fields,&$pointer)
	{
		//loop through all fields
		foreach ($fields as $Field)
		{
			//get field key
			$field_key = $Field->name;
			
			//if this field has a default value or if the valid default value should be set to NULL
			if (($Field->defaultvalue() !== NULL && $Field->nullvalue() === FALSE) || ($Field->defaultvalue() === NULL && $Field->nullvalue() === TRUE)) 
			{
				$this->assertEquals($pointer[$field_key], $Field->defaultvalue(), 'Checking default value for '.$field_key);
			}
			
			//if this field is an object or a set of objects, loop through each sub-object to look for defaults to set
			if (($Field->storage == 'object' || $Field->datatype == 'object') && $Field->fields !== NULL && is_array($Field->fields)) 
			{
				$set_sub_defaults = FALSE;
				if (isset($pointer[$field_key])) 
				{
					$send_pointer = &$pointer[$field_key];
					$set_sub_defaults = TRUE;
				}

				//recursivly run this method to check defaults on any sub-object field values
				if ($set_sub_defaults) 
				{
					//an array of objects or a keyed array of objects
					if (($Field->storage == 'object' || $Field->storage == 'array') && $Field->datatype == 'object') 
					{
					    $object_set = $send_pointer;
					    foreach ($object_set as $object_set_key => $object) {
							$this_send_pointer = &$send_pointer[$object_set_key];
						    $this->check_defaults($Field->fields,$this_send_pointer);
					    }
					}		
					//a single object
					else if ($Field->storage == 'object') 
					{
					    $this->check_defaults($Field->fields,$send_pointer);
					} 

				}
            }			
		}
	}
	
	//test filters
    public function testFilters()
    {
		//get all models to be tested
        $models = Supermodlr::get_models();
		foreach ($models as $model) 
		{
			//create a new blank object
			$model_obj = new $model['model_class']();
			
			//set test values
			$model_obj->set_test_valid_values();			
			
			//filter all test values
			$model_obj->filter();
			
			//get values
			$values = $model_obj->to_array();
			
			//get object fields
			$fields = $model_obj->get_fields();
			
			//loop through all fields and look for default values that should have been set
			$this->check_filters($fields,$values,$model_obj);
			
		}
    }	
	
	//recursive function to verify all default values were set
	public function check_filters($fields,&$pointer, $obj)
	{
		//loop through all fields
		foreach ($fields as $Field)
		{
			//get field key
			$field_key = $Field->name;
			
			//if this field has filters
            if (isset($Field->filters) && is_array($Field->filters)) 
			{
				//if this value is set (do we not run filters if the target of the filter is not set?? makes sense to me now)
				if (is_array($pointer) && isset($pointer[$field_key])) 
				{
					//loop through each filter
					foreach ($Field->filters as $method) 
					{
						//if the method is an object/method pair
						if (is_array($method)) 
						{
							//verify the result of the filter
							$args = array('value'=> $pointer[$field_key], 'key'=> $field_key, 'field'=> $Field, 'object'=> $this);
							$filtered_value = call_user_func($method,$args);
							$this->assertEquals($pointer[$field_key], $filtered_value, 'Checking filtered value for '.$field_key.' with method '.var_export($method,TRUE));								

						} 
						//if the filter is a normal php function
						else if (function_exists($method)) 
						{
							//verify the result of the filter 
							$filtered_value = $method($pointer[$field_key]);
							$this->assertEquals($pointer[$field_key], $filtered_value, 'Checking filtered value for '.$field_key.' with function '.$method);								
						}
					}
				}			
			

			}
			
			//unset non stored field values
			if ($Field->storage === FALSE) 
			{
				unset($pointer[$field_key]);
			
				//skip to next field (since stored===false fields do not need to be filtered)
				continue;
			}			

			//if this field is an object or a set of objects, loop through each sub-object to look for filters
			if (($Field->storage == 'object' || $Field->datatype == 'object') && $Field->fields !== NULL && is_array($Field->fields))
			{			
				$recursive = FALSE;
				if (isset($pointer[$field_key])) {
				    $send_pointer = &$pointer[$field_key];
 				    $recursive = TRUE;
				}

				//recursivly run this method to filter any sub-object field values
				if ($recursive) {
					//an array of objects or a keyed array of objects
					if (($Field->storage == 'object' || $Field->storage == 'array') && $Field->datatype == 'object') {
					    $array_set = $send_pointer;
						foreach ($array_set as $i => $object) 
						{
							$this_send_pointer = &$send_pointer[$i];
							$this->check_filters($Field->fields,$this_send_pointer,$obj);
						}
				    }
					//a single object
					else if ($Field->datatype == 'object') 
					{
						$this->check_filters($Field->fields,$send_pointer,$obj);
				    }  
				}
			}
			
		}
	}

	
	//test read by id
	public function testReadById() 
	{
		//get all models to be tested
        $models = Supermodlr::get_models();
		foreach ($models as $model) 
		{
			//get pk field key from obj cfg
			$pk = SupermodlrTest::$test_entry[$model['model_class']]['pk_name'];
			
			//save this id so we can remove the test entry
			$pk_value = SupermodlrTest::$test_entry[$model['model_class']]['data'][$pk];
			
			//attempt to load the object that was created in the testCreate method
			$model_obj = new $model['model_class']($pk_value);
			
			//assert model data entry was loaded
			$this->assertEquals($model_obj->loaded(), TRUE, 'testReadById reading id '.$pk_value.' from model '.$model['model_class']);
			
			//create a new test obj
			$test_obj = new $model['model_class']();
			
			//set test field values
			$test_obj->set_test_valid_values();
			
			//get values of this object
			$test_values = $test_obj->to_array();
		
			unset($test_values[$pk]);
		
			//get values from loaded obj
			$loaded_model_values = $model_obj->to_array();
			
			//unset model pk
			unset($loaded_model_values[$pk]);
			
			//assert that the saved data is the same as the test values after defaults and filters are run
			$this->assertEquals($loaded_model_values, $test_values, 'testReadById compare id '.$pk_value.' from model '.$model['model_class'].' to new object');
			
		}	
	}
	
	//test update
	public function testUpdate() 
	{
		//get all models to be tested
        $models = Supermodlr::get_models();
		foreach ($models as $model) 
		{
			//get pk field key from obj cfg
			$pk = SupermodlrTest::$test_entry[$model['model_class']]['pk_name'];
			
			//get pk value from test entry
			$pk_value = SupermodlrTest::$test_entry[$model['model_class']]['data'][$pk];
			
			//attempt to load the object that was created in the testCreate method
			$model_obj = new $model['model_class']($pk_value);
			
			//get model fields
			$fields = $model_obj->get_fields();
			
			//loop through fields 
			foreach ($fields as $Field)
			{
				$field_key = $Field->name;
				
				//skip pk field
				if ($field_key === $pk) continue;
				
				//if this field is a string, change it to a random string
				if ($Field->datatype === 'string')
				{
					$rand = substr(uniqid(),0,4);
					$model_obj->set($field_key,$rand);
					$model_obj->filter();
					SupermodlrTest::$test_entry[$model['model_class']]['updated_fields'][$field_key] = $model_obj->$field_key;
					
				}
				
				//if this field is a int, change it to a random number 1-10
				
				//@todo figure out how to test updates if there are no string fields or integer fields
				
			}
			
			//get values to be saved
			$values_to_save = $model_obj->to_array();
			
			//save new values to db
			$save_result = $model_obj->save();
			
			//assert saved ok
			$this->assertEquals($save_result->ok(), TRUE, 'testUpdate save id '.$pk_value.' from model '.$model['model_class']);
			
			//reload object
			$reloaded_model_obj = new $model['model_class']($pk_value);
			
			//get values loaded from db
			$reloaded_values = $reloaded_model_obj->to_array();
			
			//@todo only check fields that we updated so that things like auto-updated date fields aren't compared and cause a false error
			
			//assert reloaded is same as saved
			$this->assertEquals($reloaded_values, $values_to_save, 'testUpdate check values for id '.$pk_value.' from model '.$model['model_class']);
			
		}
		
	}
	
	//test query
	public function testQuery() 
	{
		//get all models to be tested
        $models = Supermodlr::get_models();
		foreach ($models as $model) 
		{
			//get pk field key from obj cfg
			$pk = SupermodlrTest::$test_entry[$model['model_class']]['pk_name'];
			
			//get pk value from test entry
			$pk_value = SupermodlrTest::$test_entry[$model['model_class']]['data'][$pk];
			
			$model_obj = new $model['model_class']();
			
			//get db drivers
			$drivers = $model_obj->cfg('drivers');
			
			//loop through all drivers
			foreach ($drivers as $i => $Driver)
			{
				$updated_field_keys = array_keys(SupermodlrTest::$test_entry[$model['model_class']]['updated_fields']);
				$updated_field_values = array_values(SupermodlrTest::$test_entry[$model['model_class']]['updated_fields']);
				$updated_field_key = $updated_field_keys[0];
				$updated_field_value = $updated_field_values[0];
				
				$where = array($updated_field_key => $updated_field_value);
				
				$result_set = $model['model_class']::query(array(
					'where'=> $where,
					'driver'=> $i
				));
				
				//assert that we retreived at least one result
				$this->assertEquals((count($result_set) > 0), TRUE, 'testQuery check db driver index '.$i.' ('.get_class($Driver).') for entry where '.var_export($where,TRUE).' from model '.$model['model_class']);
							
				
			}
		}
	}
	//test all validation rules
	//@todo figure out how to write a test for this. maybe check each field for a property of 'invalid entries' and ensure that validation fails, and check for 'test values' and ensure they are valid
	
	//test delete
	public function testDelete() 
	{
		//get all models to be tested
        $models = Supermodlr::get_models();
		foreach ($models as $model) 
		{
			//get pk field key from obj cfg
			$pk = SupermodlrTest::$test_entry[$model['model_class']]['pk_name'];
			
			//get pk value from test entry
			$pk_value = SupermodlrTest::$test_entry[$model['model_class']]['data'][$pk];
			
			//attempt to load the object that was created in the testCreate method
			$model_obj = new $model['model_class']($pk_value);
			
			//tell object to delete itself from all db's
			$delete_result = $model_obj->delete();
			
			//assert delete result status is ok
			$this->assertEquals($delete_result->ok(), TRUE, 'testDelete for entry id '.$pk_value.' from model '.$model['model_class']);
			
			//get db drivers
			$drivers = $model_obj->cfg('drivers');
						
			//build where array
			$where = array($pk => $pk_value);
						
			//loop through all drivers and ensure that the test entry was deleted
			foreach ($drivers as $i => $Driver)
			{
				//query db for this object
				$query_result = $Driver->read(array(
					'from'=> $model_obj->cfg('db_name'),
					'where'=> $where
				));
				
				//assert query result is 0
				$this->assertEquals((count($query_result) === 0), TRUE, 'testDelete re-query for db driver index '.$i.' ('.get_class($Driver).') for entry id '.$pk_value.' from model '.$model['model_class']);
				
			}
		}
	
	}		
	
	
}