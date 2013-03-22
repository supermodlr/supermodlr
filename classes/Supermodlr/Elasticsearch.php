<?php


class Supermodlr_Elasticsearch extends Supermodlr_Db {

	private $index = NULL;

	/*
	private $last_id = NULL;

	public $creates = 0;
	public $updates = 0;
	public $reads = 0;
	public $deletes = 0;*/

	/**
	 * 
	 * @returns bool
	 */
	public function driver_connect($args = array()) {
		$definitions = array(
			'host' => array(
				'default' => 'develastic01', // @todo change this
				'type'    => 'string',
			),
			'dbname' => array(
				'default' => 'basecms', // @todo change this
				'type'    => 'string',
			),
			'port' => array(
				'default' => 9200, // @todo change this
				'type'    => 'int',
			),
		);
		Args::check($args, $definitions);
		foreach ($args as $var => $val) $$var = $val;

		// Create the connection if it doesn't exist
		if (is_null($this->connection))
		{
			try
			{
				$this->connection = new \Elastica\Client(array('host' => $host, 'port' => $port));
			}
			catch (Exception $e) 
			{
				// @todo is this how errors should be handled?
				$this->set_error($e);
				return;
			}
		}

		// Initialize the index
		if (is_null($this->index))
		{
			$this->index_init($dbname);
		}

		// Return TRUE if a connection was made. Return FALSE if the connection failed.
		return ( ! is_null($this->connection) && ! is_null($this->index));
	}

	/**
	 * index_init
    * 
    * @param mixed $index_name Description.
    *
    * @access public
    *
    * @return mixed Value.
    */
	public function index_init($index_name)
	{
			// Initialize the index
			try
			{
				$this->index = $this->connection->getIndex($index_name);
			}
			catch (Exception $e)
			{
				$this->set_error($e);
				return;
			}

			// Create the index (database) if it doesn't exist
			if ( ! $this->index->exists())
			{
				$this->index_create();
			}
			else
			{
				// @todo Update existing index if necessary by comparing to new parameters
			}
	}

    /**
     * index_create
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function index_create()
	{
		// @todo make the below configurable
		$params = array(
			'number_of_shards' => 4,
			'number_of_replicas' => 1,
			'analysis' => array(
				'analyzer' => array(
					'indexAnalyzer' => array(
						'type' => 'custom',
						'tokenizer' => 'standard',
						'filter' => array('lowercase', 'mySnowball')
					),
					'searchAnalyzer' => array(
						'type' => 'custom',
						'tokenizer' => 'standard',
						'filter' => array('standard', 'lowercase', 'mySnowball')
					)
					),
					'filter' => array(
						'mySnowball' => array('type' => 'snowball','language' => 'English')
					)
				)
			);

		$delete = FALSE;
		try
		{
			$this->index->create($params, $delete);
		}
		catch (Exception $e)
		{
			$this->set_error($e);
		}
	}

	/**
	 * @returns bool
	 */
	public function driver_close($params = array()) {
	
	}

	/**
	  * @param string $from required table name
	  * @param array $columns = '*' field names to return. can be formatted array('col1','col2') or array(array('column'=> 'col1','alias'=> 'column1'),
	  * @param array $where = NULL format: array($col => $val)
	  * @param array|string $order = NULL
	  * @param bool $count = FALSE only return the count of found items	  
	  * @param int $limit = NULL
	  * @param int $skip = 0
	  * @param bool $slaveOkay = $this->slaveOkay
	  * @returns resource
	  */
	public function driver_read($args = array()) 
	{
		$definitions = array(
			'from' => array(
				'required' => TRUE,
				'type'     => 'string',
			),
			'query' => array(
				'default'  => '',
				'type'     => 'string',
			),
			'columns' => array(
				'default'  => array(),
				'type'     => 'array',
			),
			'where' => array(
				'default'  => array(),
				'type'     => 'array',
			),
			'order' => array(
				'default'  => NULL,
				'type'     => array('null', 'array'),
			),
			'count' => array(
				'default'  => FALSE,
				'type'     => 'bool',
			),
			'limit' => array(
				'default'  => 100,
				'type'     => array('null','int'),
			),
			'skip' => array(
				'default'  => 0,
				'type'     => array('int'),
			),
			'model' => array(
				'default' => array(),
				'type'    => 'object',
			),
			'fields' => array(
				'required' => TRUE,
				'type'    => 'array',
			),
		);
		Args::check($args, $definitions);
		foreach ($args as $var => $val) $$var = $val;

		// Create connection if not set
		if (is_null($this->connection))
		{
			$connected = $this->connect();
			if (!$connected)
				return FALSE;
		}

		// Select the index
		try
		{
			$this->index_init('basecms');
		}
		catch (Exception $e)
		{
			// @todo handle this error
			return FALSE;
		}
		
		// Convert regexp
		// @todo make this check all levels of $where for $regexp (recursively)
		foreach ($where as $key => $val) 
		{
			if (is_array($val) && isset($val['$regex']))
			{
				// @todo regex support
				//$where[$key] = new MongoRegex($val['$regex']);
			}
		}
		
		$dbsort = array();
		if ( isset($order) && ! empty($order)) 
		{
			foreach ($order as $sort_field => $sort_val) 
			{
				if (is_numeric($sort_field) && strpos($sort_val, ' ') !== FALSE) 
				{
					list($sort_field, $sort_val) = explode(" ", trim($sort_val));
				}
				$dbsort[$sort_field] = ($sort_val === 'asc') ? 1 : -1;
			}
		}

		// Define a Query. We want a string query.
		$QueryString = (empty($query)) ? array() : new Elastica\Query\QueryString($query);

		//$QueryString->setUseDismax(TRUE);

		// @todo 'And' or 'Or' (default : 'Or')
		//$elasticaQueryString->setDefaultOperator('AND');

		// Create the actual search object with some data.
		$Query = new Elastica\Query($QueryString);
		fbl($skip, $limit);
		$Query->setFrom($skip)->setLimit($limit);

		// Search on the index.
		$ResultSet = $this->index->search($Query);
		fbl($ResultSet, 'results');

		// If $count === TRUE, return the number of hits
		if ($count) 
			return $ResultSet->getTotalHits();
		

		die();

		// Increment read count
		$this->reads++;

		// Result sets should never be too big as they have to be loaded into memory
		return $data_arry;
	}
	
	/**
	  * @param string $into required
	  * @param array $set required	  
	  * @param bool|int $safe = $this->safe	  
	  * @param bool $fsync = this->fsync	  
	  * @returns mixed (bool === FALSE on failure, MongoId on success)	  
	  */
	public function driver_create($args = array()) 
	{
		$definitions = array(
			'into' => array(
				'required' => TRUE,
				'type'    => 'string',
			),
			'set' => array(
				'required' => TRUE,
				'type'    => 'array',
			),
			'model' => array(
				'default' => array(),
				'type'    => 'object',
			),
			'fields' => array(
				'required' => TRUE,
				'type'    => 'array',
			),
		);
		Args::check($args, $definitions);
		foreach ($args as $var => $val) $$var = $val;

		// Get the insert id from the primary db driver
		// @todo Handle conversion for objects or other types (currently assuming a MongoId class)
		$args['set']['_id'] = (string) $args['model']->get_primary_db()->insert_id();

		// Pass to driver_update() method
		return $this->driver_update($args);
	}

	/**
	 * @param string $into required table name
	 * @param array $set required array($col=> $val)
	 * @param array $where = NULL format: array($col => $val)
	 * @param int $limit = NULL  
	 * @returns mixed (bool === FALSE if failed || int of affected records)
	 */
	public function driver_update($args = array()) 
	{
		$definitions = array(
			'into' => array(
				'required' => TRUE,
				'type'    => 'string',
			),
			'set' => array(
				'required' => TRUE,
				'type'    => 'array',
			),
			'where' => array(
				'default' => array(),
				'type'    => 'array',
			),
			'model' => array(
				'default' => array(),
				'type'    => 'object',
			),
			'fields' => array(
				'required' => TRUE,
				'type'    => 'array',
			),
		);
		Args::check($args, $definitions);
		foreach ($args as $var => $val) $$var = $val;

		// Create connection if not set
		if (is_null($this->connection))
		{
			// If connection fails, return FALSE
			// @todo throw exception
			if (!$this->connect())
				return FALSE;
		}

		// Create a type (table)
		$type = $this->index->getType($into);

		// Define mapping
		$mapping = new \Elastica\Type\Mapping();
		$mapping->setType($type);
		$mapping->setParam('index_analyzer', 'indexAnalyzer');
		$mapping->setParam('search_analyzer', 'searchAnalyzer');

		foreach ($fields as $field_key => $Field)
		{
			$property = array();

			// Generate the mapping
			if ($Field->datatype == 'string')
			{
				$property['type'] = 'string';
			}
			elseif ($Field->datatype == 'int')
			{
				$property['type'] = 'integer';
			}
			else
			{
				fbl($Field->datatype.' not defined!!!');
				continue;
			}

			// @todo Conditionally include this field in the "_all" field for ease of searching
			$property['include_in_all'] = TRUE;
			
			// Add this property to the list
			$properties[$field_key] = $property;

		}

		// Set the mapping properties
		$mapping->setProperties($properties);

		// Send mapping to type
		$response = $mapping->send();

		// Get the document
		$doc = $this->get_doc($set, $fields);

		// Generate the document object
		$Document = new \Elastica\Document($doc['_id'], $doc);

		// Add the document
		$response = $type->addDocument($Document);

		// Refresh the index
		$response = $type->getIndex()->refresh();

		$this->updates++;

		// If safe, we can get the affected rows
		/*if ($params['safe'])
		{
			return $update['n'];
		}*/
		return TRUE;

	}	

    /**
     * get_doc
     * 
     * @param mixed $set    Description.
     * @param mixed $fields Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
	public function get_doc($set, $fields)
	{
		$doc = array();
		foreach ($set as $field_key => $val)
		{
			
			// @todo handle error properly if Field object is not found
			if (!isset($fields[$field_key])) {
				//$this->set_error('Field "'.$field_key.'" not found within $fields parameter.');
				return FALSE;
			}

			$Field = $fields[$field_key];
			if ($field_key == '_id' || $Field->searchable)
			{
				// @todo handle multiple languages & revisions
				$doc[$field_key] = $val;
			}
		}
		return $doc;
	}

	/**
	 * driver_delete
	 *
	 * @param string $from required table name
	 * @param array $where = NULL format: array($col => $val)	 
	 * @param int $limit = NULL
	 * @param bool|int $safe = $this->safe	  
	 * @param bool $fsync = this->fsync	  	  
	 * @returns mixed (bool === FALSE if failed || int of affected records)	  
	 */
	public function driver_delete($params = array()) 
	{
		fbl('Supermodlr_Elasticsearch->driver_delete()');

	}	
	
	/**
	  * @param array $result required 
	  * @returns int row count of rows affected
	  */
	public function driver_affected_rows($result) 
	{
		fbl('Supermodlr_Elasticsearch->driver_affected_rows()');
		return (is_array($result) && isset($result['n'])) ? $result['n'] : NULL ;
	}
	
	/**
	  * @returns mixed last inserted id
	  */
	public function driver_insert_id() 
	{
		fbl('Supermodlr_Elasticsearch->driver_insert_id()');
		return $this->last_id;
	}
	
	/**
	  *
	  * @returns array('code'=> $code, 'message'=> $message)	  
	  */
	public function driver_error($params = array()) 
	{
		fbl('Supermodlr_Elasticsearch->driver_error()');
	}	
	
	/**
	  *
	  * @returns mixed (value is used to insert a datetime value into a db.  can be string, int, or object) 
	  */
	public function driver_datetime_todb($params) 
	{
		fbl('Supermodlr_Elasticsearch->driver_datetime_todb()');
	}

	/**
	  *
	  * @returns unix timestamp of a datetime from the db
	  */
	public function driver_datetime_fromdb($params) 
	{
		//$params['value'] = new DateTime($params['value']->sec);
		fbl('Supermodlr_Elasticsearch->driver_datetime_fromdb()');
	}
	
	
	/**
	  *
	  * @returns mixed (value is used to insert a microtime value into a db.  can be string, int, or object) 
	  */
	public function driver_microtime_todb($microtime) 
	{
		fbl('Supermodlr_Elasticsearch->driver_microtime_todb()');
	}	
	
	/**
	  *
	  * @returns unix micro timestamp of a microtime from the db
	  */
	public function driver_microtime_fromdb($microtime) 
	{
		fbl('Supermodlr_Elasticsearch->driver_microtime_fromdb()');
	}
}