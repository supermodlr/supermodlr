<?php defined('SYSPATH') or die('No direct script access.');

trait Trait_FieldDataType_Relationship {

	public function validate_datatype($value) 
	{
		
		if (is_array($value))
		{
			return (isset($value['model']) && is_string($value['model']) && isset($value['_id']) && count($value) === 2);
		}
		else if ($value instanceof Supermodlr && isset($this->source) && is_array($this->source) && $value->pk_isset() && $value->pk_value() !== NULL) 
		{

			$ok = FALSE;
			foreach ($this->source as $source)
			{
				$model_class_name = Supermodlr::name_to_class_name($source['model']);
				if ($value instanceof $model_class_name)
				{
					$ok = TRUE;
					break;
				}
			}
			return $ok;
		}
		else
		{
			return FALSE;
		}


	}

   /**
    * set_value sets a value onto a 
    * 
    * @param mixed $value Description.
    *
    * @access public
    *
    * @return mixed Value.
    */
	public function set_value($value, $args = NULL)
	{
		if ($this->validate_datatype($value) === FALSE) 
		{
			throw new Exception('Invalid value, cannot set');
		}

		if (is_array($value))
		{
			$model_class_name = Supermodlr::name_to_class_name($value['model']);
			$Dummy_Model = $model_class_name::factory();
			$pk = $Dummy_Model->cfg('pk_name');
			$Dummy_Model->$pk = $value['_id'];
			return $Dummy_Model;
		}
		else if ($value instanceof Supermodlr) 
		{
			return $value; 
		}
		// This should never happen since validate_datatype should catch this condition, but we'll check for it anyway
		else
		{
			throw new Exception('Invalid value, cannot set');
		}

	}

	public function export_value($value, $args = NULL) 
	{
		
		if ($this->validate_datatype($value) === FALSE) 
		
		{
			throw new Exception('Invalid value, cannot return for export');
		}

		if (is_array($value))
		{
			// if we want to expand this relationship
			if (is_array($args) && isset($args['expand']) && $args['expand'] === TRUE)
			{
				// get the model class name
				$model_class_name = Supermodlr::name_to_class_name($value['model']);

				// load the related model
				$Model = $model_class_name::factory($value['_id']);

				// we cannot pass expand=true or we'll get into a never ending recursive loop
				$args['expand'] = FALSE;
				$args['type'] = 'export';
				// return the full related object
				return $Model->to_array($args);
			}
			// if we are not expanding, just return the array version of the relationship
			else
			{
				return $value;
			}

		}
		else if ($value instanceof Supermodlr)
		{
			// if we want to expand this relationship
			if (is_array($args) && isset($args['expand']) && $args['expand'] === TRUE)
			{
				// we cannot pass expand=true or we'll get into a never ending recursive loop
				$args['expand'] = FALSE;
				$args['type'] = 'export';
				// return the full related object
				return $value->to_array($args);	
			}
			else
			{
				return array('model'=> $value->get_name(), '_id'=> $value->pk_value());
			}			

		}

	}

	public function storage_value($value, $args = NULL) 
	{

		if ($this->validate_datatype($value) === FALSE) 
		{
			throw new Exception('Invalid value, cannot return for storage');
		}

		if (is_array($value))
		{
			// if we want to expand this relationship
			if (is_array($args) && isset($args['expand']) && $args['expand'] === TRUE)
			{
				// get the model class name
				$model_class_name = Supermodlr::name_to_class_name($value['model']);

				// load the related model
				$Model = $model_class_name::factory($value['_id']);

				// we cannot pass expand=true or we'll get into a never ending recursive loop
				$args['expand'] = FALSE;
				$args['type'] = 'storage';
				// return the full related object
				return $Model->to_array($args);
			}
			// if we are not expanding, just return the array version of the relationship
			else
			{
				return $value;
			}
		}
		else if ($value instanceof Supermodlr)
		{
			// if we want to expand this relationship
			if (is_array($args) && isset($args['expand']) && $args['expand'] === TRUE)
			{
				// we cannot pass expand=true or we'll get into a never ending recursive loop
				$args['expand'] = FALSE;
				$args['type'] = 'storage';
				// return the full related object
				return $value->to_array($args);				
			}
			else
			{
				return array('model'=> $value->get_name(), '_id'=> $value->pk_value());
			}
		}
	}
}