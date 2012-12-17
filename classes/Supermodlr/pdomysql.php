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
	  * @param array $fields = '*' field names to return. can be formatted array('col1','col2') or array(array('column'=> 'col1','alias'=> 'column1'),
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
	
		//from
		
		//fields
		$fields_sql = '*';
		if (isset($params['fields']) && is_array($params['fields']) && !empty($params['fields'])) {
			$field_sql_set = array();
			foreach ($params['fields'] as $field) {
				if (is_string($field)) {
					$field_sql_set[] = $field;
				} else if (is_array($field)) {
					$field_sql_set[] = $field['column'].' as '.$field['alias'];
				}
			}
			if (!empty($field_sql_set)) {
				$fields_sql = implode(', ',$field_sql_set);
			} else {
				$fields_sql = '*';
			}
			
		//select all fields if none are specified			
		} else {
			$fields_sql = '*';
		}
		
		//where
		$where_sql = $this->where_to_sql($params);
		
		if ($params['prepared'] == TRUE) 
		{
			if (isset($params['where']) && count($params['where']) > 0)
			{
				$where_values = array_values($params['where']);
			}
			else
			{
				$where_values = array();
			}
			
		}
		
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
		
		$sql = 'select '.$fields_sql.' from '.$params['from'].' '.$where_sql.' '.$order_sql.' '.$limit_sql.';';

		//if this is not a prepared statement, execute it
		if ($params['prepared'] === FALSE) {
		
			$data = FALSE;
		//this can be a prepared statement
		} else {
			//generate sql hash
			$sql_hash = md5($sql);
			//if statment hasn't been prepared yet
			if (!isset($this->statements[$sql_hash])) {
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
					$where_set[] = $col." = ?";
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
	public function driver_datetime_todb($params = array()) 
	{
	
	}

	/**
	  *
	  * @returns unix timestamp of a datetime from the db
	  */
	public function driver_datetime_fromdb($params = array()) 
	{
	
	}
	
	
	/**
	  *
	  * @returns mixed (value is used to insert a microtime value into a db.  can be string, int, or object) 
	  */
	public function driver_microtime_todb($params = array()) 
	{
	
	}	
	
	/**
	  *
	  * @returns unix micro timestamp of a microtime from the db
	  */
	public function driver_microtime_fromdb($params = array()) 
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
