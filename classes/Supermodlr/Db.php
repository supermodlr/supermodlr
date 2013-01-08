<?php


class Supermodlr_Db {

	protected $connection = NULL;
	protected $error = NULL;	
	protected $errors = array();		
	protected $host = 'localhost';
	protected $port = '0';
	protected $dbname = 'test';
	protected $user = '';
	protected $pass = '';
	protected $transactions = FALSE;
	protected $use_prepared = FALSE;
	
	public function __construct($params = array()) 
	{
		foreach ($params as $key => $value)
		{
			$this->$key = $value;
		}
	}
	
	/**
	  * 
	  * @returns bool
	  */
	public function connect($params = array()) 
	{
	   return $this->driver_connect($params);
	}

	/**
	  *
	  * @returns bool	  
	  */
	public function close($params = array()) 
	{
	   return $this->driver_close($params);
	}	
	
	/**
	  *
	  * @returns mixed (bool === FALSE on failure, mixed insert_id() on success)	  
	  */
	public function create($params = array()) 
	{

		if (isset($params['fields']) && isset($params['model']))
		{
			foreach ($params['fields'] as $field_key => $Field)
			{
				if (isset($params['set'][$field_key]) && method_exists($this, $Field->datatype.'_todb'))
				{
					$method = $Field->datatype.'_todb';
					$this->$method(array(
						'value'=> &$params['set'][$field_key],
						'set'  => &$params['set'],
						'field'=> $Field,
						'model'=> $params['model'],
					));

				}
			}
		}
		$create = $this->driver_create($params);
	    return $create;
	}

	/**
	  *
	  * @returns array || resource
	  */
	public function read($params = array()) 
	{
		//@todo handle standard caching here

		if (isset($params['fields']) && isset($params['model']))
		{
			foreach ($params['fields'] as $field_key => $Field)
			{
				if (isset($params['where'][$field_key]) && method_exists($this, $Field->datatype.'_todb'))
				{
					$method = $Field->datatype.'_todb';
					$this->$method(array(
						'value'=> &$params['where'][$field_key],
						'set'  => &$params['where'],
						'field'=> $Field,
						'model'=> $params['model'],
					));

				}
			}
		}

		$result = $this->driver_read($params);

		if (isset($params['fields']) && isset($params['model']) && is_array($result))
		{
			foreach ($result as $i => $row) {
				foreach ($params['fields'] as $field_key => $Field)
				{
					if (method_exists($this, $Field->datatype.'_fromdb'))
					{
						$method = $Field->datatype.'_fromdb';
						$this->$method(array(
							'value'  => &$result[$i][$field_key],
							'result' => &$result[$i],
							'field'  => $Field,
							'model'  => $params['model'],
						));
					}
				}
			}

		}		

	   	return $result;
	}	
	
	/**
	  *
	  * @returns mixed (bool === FALSE if failed || int of affected records)
	  */
	public function update($params = array()) 
	{
		if (isset($params['fields']) && isset($params['model']))
		{
			foreach ($params['fields'] as $field_key => $Field)
			{
				if (isset($params['set'][$field_key]) && method_exists($this, $Field->datatype.'_todb'))
				{
					$method = $Field->datatype.'_todb';
					$this->$method(array(
						'value'=> &$params['set'][$field_key],
						'set'  => &$params['set'],
						'field'=> $Field,
						'model'=> $params['model'],
					));

				}
			}
		}		
	   return $this->driver_update($params);
	}	
	
	/**
	  *
	  * @returns mixed (bool === FALSE if failed || int of affected records)	  
	  */
	public function delete($params = array()) 
	{
	   return $this->driver_delete($params);
	}	
	
	/**
	  *
	  * @returns mixed (bool === FALSE if failed || int of affected records)	  
	  */
	public function affected_rows($result) 
	{
	   return $this->driver_affected_rows($result);
	}		
	
	/**
	  *
	  * @returns statement	  
	  */
	public function prepare($params = array()) 
	{
	   return $this->driver_prepare($params);
	}
	
	/**
	  *
	  * @returns mixed (bool === FALSE if failed || mixed value if id was retrieved)	  
	  */
	public function insert_id($params = array()) 
	{
	   return $this->driver_insert_id($params);
	}
	
	/**
	  *
	  * @returns array('code'=> $code, 'message'=> $message)	  
	  */
	public function set_error($Error) 
	{
		$this->error = $Error;
		$this->errors[] = $Error;
		throw new Exception($Error->getMessage());
	}	
	
	/**
	  *
	  * @returns Exception $Error	  
	  */
	public function error($params = array()) 
	{
		return $this->error;
	}	
	
	/**
	  *
	  * @returns all Exception $Error	  
	  */
	public function errors($params = array()) 
	{
		return $this->errors;
	}	
	
	/**
	  *
	  * @returns mixed (value is used to insert a datetime value into a db.  can be string, int, or object) 
	  */
	public function datetime_todb($params) 
	{
	   return $this->driver_datetime_todb($params);
	}

	/**
	  *
	  * @returns unix timestamp of a datetime from the db
	  */
	public function datetime_fromdb($params) 
	{
	   return $this->driver_datetime_fromdb($params);
	}
	
	
	/**
	  *
	  * @returns mixed (value is used to insert a microtime value into a db.  can be string, int, or object) 
	  */
	public function microtime_todb($params) 
	{
	   return $this->driver_microtime_todb($params);
	}	
	
	/**
	  *
	  * @returns unix micro timestamp of a microtime from the db
	  */
	public function microtime_fromdb($params) 
	{
	   return $this->driver_microtime_fromdb($params);
	}	
	
	/**
	  *
	  * @returns bool 	  
	  */
	public function start_transaction($params = array()) 
	{
	   return $this->driver_start_transaction($params);
	}
	
	
	/**
	  *
	  * @returns bool 	  
	  */
	public function in_transaction($params = array()) 
	{
	   return $this->driver_in_transaction($params);
	}
	
	/**
	  *
	  * @returns bool 		  
	  */
	public function commit_transaction($params = array()) 
	{
	   return $this->driver_commit_transaction($params);
	}	
	
	/**
	  *
	  * @returns bool 		  
	  */
	public function rollback_transaction($params = array()) 
	{
	   return $this->driver_rollback_transaction($params);
	}	

	/**
	  *
	  * @returns bool 		  
	  */
	public function transaction_status() 
	{
	   return $this->driver_transaction_status();
	}	
	
	
	/**
	  *
	  * @returns bool 		  
	  */
	public function supports_transactions() 
	{
	   return $this->transactions;
	}	
}
