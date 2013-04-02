<?php defined('SYSPATH') or die('No direct script access.');
/**
  * FileDescription: SupermodlrUnifiedCollection
  */
trait Trait_SupermodlrUnifiedCollection {

	public static $__SupermodlrUnifiedCollection__scfg = array(
		'traits__SupermodlrUnifiedCollection__name' => 'SupermodlrUnifiedCollection',
		'traits__SupermodlrUnifiedCollection__label' => 'SupermodlrUnifiedCollection',
		'traits__SupermodlrUnifiedCollection__description' => 'SupermodlrUnifiedCollection',            
		'field_keys' => array(
			'ModelId',
		),
	);

	public function event__Trait_SupermodlrUnifiedCollection__save_end($params)
	{

		// Get the name of the class that has this trait
		$model_name = $this->get_trait_class('Trait_SupermodlrUnifiedCollection', $this);

		if ($this->get_model_class() == $model_name) return;

		// Instanciate the class
		// @todo Look up existing model using ModelId
		$Model = $model_name::factory(array('where' => array('ModelId' => $this->pk_value())));

		$fields = $this->get_fields();
		fbl($fields, 'fields');
		$set = array();
		foreach ($this->to_storage() as $key => $val)
		{

			if ($fields[$key]->filterable || $key == 'Name' || $key == '_id')
			{
				if ($key == '_id') $key = 'ModelId';
				fbl($val, $key);
				$Model->set($key, $val);
			}
			
		}

		$r = $Model->save();

	}

}
