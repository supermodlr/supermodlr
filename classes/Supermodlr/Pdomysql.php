<?php


class Supermodlr_Pdomysql extends Supermodlr_Db {

	protected $port = '3306';
	protected $use_prepared = TRUE;
	protected $transactions = FALSE;	
	
	/**
	  * 
	  * @returns bool
	  */
	public function driver_connect($params = array()) {
	
		if (is_null($this->connection)) {
			//host
			$host = (isset($params['host'])) ? $params['host'] : $this->host;
			
			//dbname
			$dbname = (isset($params['dbname'])) ? $params['dbname'] : $this->dbname;
			
			//port
			$port = (isset($params['port'])) ? $params['port'] : $this->port;
			
			//user
			$user = (isset($params['user'])) ? $params['user'] : $this->user;
			
			//pass
			$pass = (isset($params['pass'])) ? $params['pass'] : $this->pass;
			
			try {
			
				$this->connection = new PDO('mysql:host='.$host.':'.$port.';dbname='.$dbname, $user, $pass);
				$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
			} 
			catch (PDOException $Error) 
			{
				$this->set_error($Error);
			}
		}
		//return TRUE if a connection was made.  return FALSE if the connection failed
		return (!is_null($this->connection));
	}

	/**
	  * @returns bool	  
	  */
	public function driver_close($params = array()) {
	
	}	
	
	/**
	  * @param string $into required
	  * @param array $set required	  
	  * @param bool $prepared = TRUE
	  * @returns mixed (bool === FALSE on failure, mixed insert_id() on success)	  
	  */
	public function driver_create($params = array()) 
	{
		//create connection if not set
		if (is_null($this->connection))
		{
			$connected = $this->connect();
			if (!$connected)
			{
				return FALSE;
			}
		}
	
		//set prepared param if it was not sent
		if (!isset($params['prepared'])) 
		{
			$params['prepared'] = $this->use_prepared;
		}
	
		//set data to update
		$set_sql_arry = array();
		$prepared_values = array();
		
		//get column names for sql
		$columns = array_keys($params['set']);
		
		//generate sql fragment
		$columns_sql = ' ('.implode(',',$columns).') ';
		
		//if prepared
		$values = array();
		if ($params['prepared'] ) {
			//creates an array full of markers for prepared statement values
			foreach ($params['set'] as $k => $v) 
			{
				$values[] = '?';
			}
			$set_values = array_values($params['set']);
		//no prepared statments, get raw values
		} else {
			//loop through all values and put the values in quotes for insertion
			foreach ($set as $col=> $val) {
				$values[] = "'".$val."'";
			}
		}
		
		$values_sql = ' values('.implode(",",$values).')';
		
		//setup sql statement
		$sql = 'insert into '.$params['into'].' '.$columns_sql.' '.$values_sql.';';	
		//if this is not a prepared statement, execute it
		if ($params['prepared'] === FALSE) 
		{
		
		
		
		}
		//this can be a prepared statement		
		else 
		{
			//generate sql hash
			$sql_hash = md5($sql);
			
			//if statment hasn't been prepared yet
			if (!isset($this->statements[$sql_hash])) 
			{
				//prepare the statement
				$this->statements[$sql_hash] = $this->connection->prepare($sql);
			}
			
			try {
				//execute the statement
				$result = $this->statements[$sql_hash]->execute($set_values);

				if ($result) 
				{
					return $this->insert_id();
				}
				else 
				{
					return FALSE;
				}
			} 
			catch (PDOException $Error) //@todo put correct exception here
			{
				$this->set_error($Error);
				return FALSE;
			}
		}	
	}

	/**
	  * @param string $from required table name
	  * @param array $columns = '*' field names to return. can be formatted array('col1','col2') or array(array('column'=> 'col1','alias'=> 'column1'),
	  * @param array $where = NULL format: array($col => $val)
	  * @param array|string $order = NULL
	  * @param int $limit = NULL
	  * @param int $skip = 0
	  * @param bool $prepared = TRUE use prepared statements if TRUE, standard query if FALSE
	  * @returns resource
	  */
	public function driver_read($params = array()) 
	{
		//create connection if not set
		if (is_null($this->connection))
		{
			$connected = $this->connect();
			if (!$connected)
			{
				return FALSE;
			}
		}	
		//set prepared param if it was not sent
		if (!isset($params['prepared'])) 
		{
			$params['prepared'] = $this->use_prepared;
		}
	
		//run raw sql
		if (isset($params['sql'])) 
		{
			if ($params['prepared'] && isset($params['values']))
			{
				$sql = $params['sql'];

				//generate sql hash
				$sql_hash = md5($sql);
				//if statment hasn't been prepared yet
				if (!isset($this->statements[$sql_hash])) 
				{
					//prepare the statement
					$this->statements[$sql_hash] = $this->connection->prepare($sql);
				}
				//execute the statement
				$result = $this->statements[$sql_hash]->execute($params['values']);
				if ($result) 
				{
					$data = $this->statements[$sql_hash]->fetchAll(PDO::FETCH_ASSOC);
				}
				else 
				{
					$data = FALSE;
				}		
				return $data;		
			} 
			else
			{
				$result = $this->connection->query($params['sql']);
				if ($result) 
				{
					$data = $result->fetchAll(PDO::FETCH_ASSOC);
				}
				else 
				{
					$data = FALSE;
				}
				return $data;	
			}

		}
		//from
		
		//columns
		$columns_sql = '*';
		if (isset($params['columns']) && is_array($params['columns']) && !empty($params['columns'])) {
			$columns_sql_set = array();
			foreach ($params['columns'] as $columns) {
				if (is_string($columns)) {
					$columns_sql_set[] = $columns;
				} else if (is_array($columns)) {
					$columns_sql_set[] = $columns['column'].' as '.$columns['alias'];
				}
			}
			if (!empty($columns_sql_set)) {
				$columns_sql = implode(', ',$columns_sql_set);
			} else {
				$columns_sql = '*';
			}
			
		//select all columns if none are specified			
		} else {
			$columns_sql = '*';
		}
		
		//where
		$where_sql_params = $params;
		$where_values = array();
		//assign 'where' by reference so it can be modified inside 'where_to_sql'
		$where_sql_params['where'] = &$params['where'];

		$where_sql_params['where_values'] = &$where_values;
		$where_sql = $this->where_to_sql($where_sql_params);
		
		//order
		$order_sql = '';
		if (isset($params['order']) && !empty($params['order'])) {
			if (is_string($params['order'])) {
				$order_sql = ' order by '.$params['order'];
			}
		} else {
			$order_sql = '';
		}
		
		//limit
		if (isset($params['limit'])) {
		   $limit_sql = 'limit '.$params['limit'];		
		
			//skip 
			if (isset($params['skip'])) {
				$limit_sql .= ','.$params['skip'];
			}
			
		//no limit
		} else {
			//skip 
			if (isset($params['skip'])) {
				$limit_sql = 'limit 0,'.$params['skip'];
			} else {
				$limit_sql = '';
			}			
		}
		
		$sql = 'select '.$columns_sql.' from '.$params['from'].' '.$where_sql.' '.$order_sql.' '.$limit_sql.';';

		//if this is not a prepared statement, execute it
		if ($params['prepared'] === FALSE) {
		
			$data = FALSE;
		//this can be a prepared statement
		} else {
			//generate sql hash
			$sql_hash = md5($sql);
			//if statment hasn't been prepared yet
			if (!isset($this->statements[$sql_hash])) 
			{
				//prepare the statement
				$this->statements[$sql_hash] = $this->connection->prepare($sql);
			}
			//execute the statement
			$result = $this->statements[$sql_hash]->execute($where_values);
			if ($result) 
			{
				$data = $this->statements[$sql_hash]->fetchAll(PDO::FETCH_ASSOC);
			}
			else 
			{
				$data = FALSE;
			}
		}
		return $data;
	}	
	
	/**
	  * @param string $from required table name
	  * @param array $set required array($col=> $val)
	  * @param array $where = NULL format: array($col => $val)
	  * @param int $limit = NULL
	  * @param bool $prepared = TRUE use prepared statements if TRUE, standard query if FALSE
	  * @returns mixed (bool === FALSE if failed || int of affected records)
	  */
	public function driver_update($params = array()) 
	{
		//create connection if not set
		if (is_null($this->connection))
		{
			$connected = $this->connect();
			if (!$connected)
			{
				return FALSE;
			}
		}	
	
		//set prepared param if it was not sent
		if (!isset($params['prepared'])) 
		{
			$params['prepared'] = $this->use_prepared;
		}	
		//set data to update
		$set_sql_arry = array();
		$prepared_values = array();
		
		//loop through all 'set' values
		foreach ($params['set'] as $col => $val) {
		
			//if we support prepared statements
			if ($params['prepared']) {
				//store columns and value positions for prepared query
				$set_sql_arry[] = $col.' = ?';
				//store values for prepared query
				$prepared_values[] = $val;
				
			//no prepared statements
			} else {
				//store columns and values in array to be added to sql update statement
				$set_sql_arry[] = $col.' = ?';
			}
		}
		$set_sql = implode(',',$set_sql_arry);
		
		//where
		$where_sql = $this->where_to_sql($params);
		
		//setup sql statement
		$sql = 'update '.$params['into'].' set '.$set_sql.' '.$where_sql.';';
		//if this is not a prepared statement, execute it
		if ($params['prepared'] === FALSE) {
		
		
		//this can be a prepared statement
		} else {
			$where_values = array_values($params['where']);
			
			$prepared_values = array_merge($prepared_values,$where_values);
		
			//generate sql hash
			$sql_hash = md5($sql);
			//if statment hasn't been prepared yet
			if (!isset($this->statements[$sql_hash])) {
				//prepare the statement
				$this->statements[$sql_hash] = $this->connection->prepare($sql);
			}
			//execute the statement
			$result = $this->statements[$sql_hash]->execute($prepared_values);
			
			if ($result) {
				return $this->affected_rows($this->statements[$sql_hash]);
			} else {
				return FALSE;
			}
		}		
	}	
	
	/**
	  *
	  * @returns mixed (bool === FALSE if failed || int of affected records)	  
	  */
	public function driver_delete($params = array()) 
	{
		//create connection if not set
		if (is_null($this->connection))
		{
			$connected = $this->connect();
			if (!$connected)
			{
				return FALSE;
			}
		}	
	
		//set prepared param if it was not sent
		if (!isset($params['prepared'])) 
		{
			$params['prepared'] = $this->use_prepared;
		}	
		//where
		$where_sql = $this->where_to_sql($params);
		
		//setup sql statement
		$sql = 'delete from '.$params['from'].' '.$where_sql.';';
		//if this is not a prepared statement, execute it
		if ($params['prepared'] === FALSE) {
		
		
		//this can be a prepared statement
		} else {
			$where_values = array_values($params['where']);
		
			//generate sql hash
			$sql_hash = md5($sql);
			//if statment hasn't been prepared yet
			if (!isset($this->statements[$sql_hash])) {
				//prepare the statement
				$this->statements[$sql_hash] = $this->connection->prepare($sql);
			}
			//execute the statement
			$result = $this->statements[$sql_hash]->execute($where_values);
			
			if ($result) {
				return $this->affected_rows($this->statements[$sql_hash]);
			} else {
				return FALSE;
			}
		}	
	}	
	
	/**
	  * @param array $where required format: array($col => $val)
	  * @param bool $prepared required true if this should return a sql string fragment for a prepared statement
	  * @returns string returns sql string	  
	  */
	public function where_to_sql($params = array()) 
	{
		$where_sql = '';
		$where_values = array();
		//if where conditions were sent
		if (isset($params['where']) && is_array($params['where']) && !empty($params['where'])) 
		{
			$where_set = array();
			//loop through all where conditions
			foreach ($params['where'] as $col => $val) 
			{
				//if we are using prepared statements
				if ($params['prepared'] === FALSE) 
				{
					$where_set[] = $col." = '".$val."'";
				}
				//raw sql statements
				else 
				{

					//regexp
					if (is_array($val) && isset($val['$regex']))
					{
						//skip the first '/'
						$mysql_regexp = substr($val['$regex'], 1);
						
						//remove the end '/' and any operators that were set
						$mysql_regexp = preg_replace('/\/[a-z]*$/i','',$mysql_regexp);

						$where_set[] = $col." REGEXP '".$mysql_regexp."'";
						
					}
					else
					{
						$where_set[] = $col." = ?";
						$params['where_values'][] = $val;
					}

					
				}
			}
			//generate sql where fragment
			$where_sql = ' where '.implode(' and ',$where_set);
		} 
		else 
		{
			$where_sql = '';
		}

		return $where_sql;
	}	

	/**
	  * Converts arrays and keyed arrays, objects, and relationships to expected table column format
	  * @param array $model required format: array($col => $val)	  	  
	  * @param array $fields required format: array($col => $val)
	  * @param array $values required format: array($col => $val)	  
	  * @returns array returns array of values flattened for an sql table
	  */
	/*public function fields_to_sql($params = array()) 
	{
		$new_values = array();
		foreach ($values as $field_key => $value)
		{
			$Field = $fields[$field_key];
			//convert single storage relationships
			if ($Field->datatype == 'relationship' && $Field->storage == 'single')
			{
				if (isset($value['model']) && isset($value['_id']))
				{
					$new_values[$field_key.'__'] = $value['model'];
					$new_values[$field_key.'__'] = $value['_id'];
				}
				else
				{
					$new_values[$field_key.'__model'] = NULL;
					$new_values[$field_key.'__id'] = NULL;
				}
				
			}
			//@todo convert any datatype == 'objects' fields

			//@todo convert any storage == 'array' fields

			//@todo convert any storage == 'keyed_array' fields

			//no conversion needed
			else
			{
				$new_values[$field_key] = $value;
			}
		}
		return $new_values;
	}*/

	/**
	  * Converts datatype == 'relationship' to expected table column format
	  * @param Object Model $model required format: array($col => $val)	  	  
	  * @param Object Field $field required format: array($col => $val)
	  * @param mixed $value required reference  
	  * @param mixed $set required reference
	  * @returns 
	  */
	public function relationship_todb($params = array()) 
	{
		$field_key = $params['field']->name;
		if (isset($params['value']['model']) && isset($params['value']['_id']))
		{
			$params['set'][$field_key.'__model'] = $params['value']['model'];
			$params['set'][$field_key.'__id'] = $params['value']['_id'];
		}
		else
		{
			$params['set'][$field_key.'__model'] = NULL;
			$params['set'][$field_key.'__id'] = NULL;
		}
		//unset the direct value
		unset($params['set'][$field_key]);		
	}

	/**
	  * Converts datatype == 'relationship' to expected table column format
	  * @param Object Model $model required format: array($col => $val)	  	  
	  * @param Object Field $field required format: array($col => $val)
	  * @param mixed $value required reference  
	  * @param mixed $set required reference
	  * @returns 
	  */
	public function relationship_fromdb($params = array()) 
	{
		$field_key = $params['field']->name;
		if (isset($params['result'][$field_key.'__model']) && isset($params['result'][$field_key.'__model']))
		{
			$params['result'][$field_key] = array('model'=> $params['result'][$field_key.'__model'], '_id'=> $params['result'][$field_key.'__id']);
			unset($params['result'][$field_key.'__model']);
			unset($params['result'][$field_key.'__id']);
		}
	
	}

	/**
	  * @param PDOStatement $result required Pass an executed pdo statement
	  * @returns int row count of rows affected by the passed executed statement object
	  */
	public function driver_affected_rows($result) 
	{
		return $result->rowCount();
	}
	
	/**
	  * @returns mixed last inserted id
	  */
	public function driver_insert_id() 
	{
		return $this->connection->lastInsertId();
	}
	
	/**
	  *
	  * @returns array('code'=> $code, 'message'=> $message)	  
	  */
	public function driver_error($params = array()) 
	{
	
	}	
	
	/**
	  *
	  * @returns mixed (value is used to insert a datetime value into a db.  can be string, int, or object) 
	  */
	public function driver_datetime_todb($params) 
	{
		if (is_object($params['value']) && $params['value'] instanceOf DateTime)
		{
			$datetime = $params['value']->getTimestamp();
		}
		else if (is_string($params['value']) && !is_numeric($params['value'])) 
		{
			$datetime = strtotime($params['value']);
			if (!$datetime) $params['value'] = NULL;
		}
		else
		{
			$datetime = $params['value'];
		}
		$params['value'] = date("Y-m-d H:i:s", $datetime);
		
	}

	/**
	  *
	  * @returns a DateTime object from a datetime column in the db
	  */
	public function driver_datetime_fromdb($params)
	{
		try {
			$datetime = new DateTime($timestamp);
			$params['value'] = $datetime;
		}
		catch(Exception $e)
		{
			$params['value'] = NULL;
		}
		
	}
	
	
	/**
	  *
	  * @returns mixed (value is used to insert a microtime value into a db.  can be string, int, or object) 
	  */
	public function driver_microtime_todb($microtime) 
	{
	
	}	
	
	/**
	  *
	  * @returns unix micro timestamp of a microtime from the db
	  */
	public function driver_microtime_fromdb($microtime) 
	{
	
	}	
	
	/**
	  *
	  * @returns bool 	  
	  */
	public function driver_start_transaction($params = array()) 
	{
	
	}
	
	/**
	  *
	  * @returns bool 		  
	  */
	public function driver_commit_transaction($params = array()) 
	{
	
	}	
	
	/**
	  *
	  * @returns bool 		  
	  */
	public function driver_rollback_transaction($params = array()) 
	{
	
	}	

	/**
	  *
	  * @returns bool 		  
	  */
	public function driver_in_transaction($params = array()) 
	{
	
	}		
	
	/**
	  *
	  * @returns bool 		  
	  */
	public function driver_transaction_status($params = array()) 
	{
	
	}		
}
