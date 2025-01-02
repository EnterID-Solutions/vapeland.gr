<?php
class ModelToolSeoPackageEditor extends Model {
  
  public function __construct($registry) {
    parent::__construct($registry);
    
    if (version_compare(VERSION, '3', '>=')) {
      $this->url_alias = 'seo_url';
    } else {
      $this->url_alias = 'url_alias';
    }
  }
  
  /**
	 * Create the data output array for the DataTables rows
	 *
	 *  @param  array $columns Column information array
	 *  @param  array $data    Data from the SQL get
	 *  @return array          Formatted data in a row based format
	 */
	public function data_output ( $columns, $data, $type = '')
	{
		$out = array();

		for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
			$row = array();

			for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
				$column = $columns[$j];

				// Is there a formatter?
        
        if($column['db'] == 'related') {
            $related = $this->db->query("SELECT pr.related_id, pd.name FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product_description pd ON pd.product_id = pr.related_id WHERE pr.product_id='" . $data[$i]['product_id'] . "' AND pd.language_id=" . $this->config->get('config_language_id') . " ORDER BY pd.name")->rows;
            
            $old_related = $old_related_id = array();
            foreach ($related as $rel) {
              $old_related[] = $rel['name'];
              $old_related_id[] = $rel['related_id'];
            }
            
          	$data[$i]['related']['text'] = implode(', ', $old_related);
          	$data[$i]['related']['rows'] = $related;
        }
        
				if ( isset( $column['formatter'] ) ) {
					$row[ $column['dt'] ] = $column['formatter']( $data[$i][ $column['db'] ], $data[$i], $type, $this );
				}
				else {
					$row[ $column['dt'] ] = $data[$i][ $columns[$j]['db'] ];
				}
			}

			$out[] = $row;
		}

		return $out;
	}


	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL limit clause
	 */
	public function limit ( $request, $columns )
	{
		$limit = '';

		if ( isset($request['start']) && $request['length'] != -1 ) {
			$limit = "LIMIT ".intval($request['start']).", ".intval($request['length']);
		}

		return $limit;
	}


	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL order by clause
	 */
	public function order ( $request, $columns, $default )
	{
		$order = '';

		if ( isset($request['order']) && count($request['order']) ) {
			$orderBy = array();
			$dtColumns = self::pluck( $columns, 'dt' );

			for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
				// Convert the column index into the column data property
				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];

				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['orderable'] == 'true' ) {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = '`'.$column['db'].'` '.$dir;
				}
			}
			
			if(!implode(', ', $orderBy)) return 'ORDER BY ' . $default;
			
			$order = 'ORDER BY '.implode(', ', $orderBy);
		}

		return $order;
	}


	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @param  array $bindings Array of values for PDO bindings, used in the
	 *    sql_exec() function
	 *  @return string SQL where clause
	 */
	public function filter ( $request, $columns, &$bindings )
	{
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = self::pluck( $columns, 'dt' );

		if ( isset($request['search']) && $request['search']['value'] != '' ) {
			$str = $request['search']['value'];

			for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['searchable'] == 'true' ) {
					$binding =  '\'%'. $this->db->escape($str) .'%\'';
					$globalSearch[] = "`".$column['db']."` LIKE ".$binding;
				}
			}
		}

		// Individual column filtering
		for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $request['columns'][$i];
			$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $columns[ $columnIdx ];

			$str = $requestColumn['search']['value'];

			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$binding =  '\'%'. $this->db->escape($str) .'%\'';
				$columnSearch[] = "`".$column['db']."` LIKE ".$binding;
			}
		}

		// Combine the filters into a single string
		$where = '';

		if ( count( $globalSearch ) ) {
			$where = '('.implode(' OR ', $globalSearch).')';
		}

		if ( count( $columnSearch ) ) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where .' AND '. implode(' AND ', $columnSearch);
		}

		if ( $where !== '' ) {
			$where = 'WHERE ' . $where;
			
      if($this->filter_language !== false) {
        $where .= " AND ";
      }
		} else if($this->filter_language !== false) {
      $where .= " WHERE ";
    }
		
		if($this->filter_language !== false) {
      $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE status = '1'");

      foreach ($query->rows as $result) {
        if ($result['code'] == $this->config->get('config_language')) $default_lang = $result['language_id'];
      }
      
      if ($this->filter_language == $default_lang) {
        $where .= " ( language_id = '" . $this->filter_language . "' OR language_id = '0' )";
      } else {
        $where .= " language_id = '" . $this->filter_language . "'";
      }
    }
    
		return $where;
	}


	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $sql_details SQL connection details - see sql_connect()
	 *  @param  string $table SQL table to query
	 *  @param  string $primaryKey Primary key of the table
	 *  @param  array $columns Column information array
	 *  @return array          Server-side processing response array
	 */
	public function simple ( $request, $type, $lang, $store, $columns )
	{
		$bindings = array();
		
		$extra_select = '';
		$extra_join = '';
		$extra_where = '';
		$primaryKey = $type . '_id';
    
		if (in_array($type, array('product', 'category', 'information'))) {
			$select_cols = "`".implode("`, `", self::pluck_fields($columns, 'db'))."`";
      
      if ($store) {
        $table = '`'.DB_PREFIX . "seo_" . $type . "_description` INNER JOIN `" . DB_PREFIX . $type . "` USING(" . $primaryKey . ")";
      } else {
			  $table = '`'.DB_PREFIX . $type . "_description` INNER JOIN `" . DB_PREFIX . $type . "` USING(" . $primaryKey . ")";
      }
      
			$this->filter_language = $lang;
			$default_order = 'name';
      
      // @perf: high impact
      if (version_compare(VERSION, '3', '>=') || ($this->config->get('mlseo_multistore') && $this->config->get('mlseo_ml_mode'))) {
        $extra_select .= ",(SELECT keyword FROM " . DB_PREFIX . $this->url_alias . " u WHERE query = CONCAT('".$type."_id=' , ".$type."_id) AND (language_id = '".(int) $lang."' OR language_id = 0) AND store_id = ".$store." LIMIT 1) AS seo_keyword";
      } else if ($this->config->get('mlseo_multistore')) {
        $extra_select .= ",(SELECT keyword FROM " . DB_PREFIX . $this->url_alias . " u WHERE query = CONCAT('".$type."_id=' , ".$type."_id) AND store_id = ".$store." LIMIT 1) AS seo_keyword";
      } else if ($this->config->get('mlseo_ml_mode')) {
        $extra_select .= ",(SELECT keyword FROM " . DB_PREFIX . $this->url_alias . " u WHERE query = CONCAT('".$type."_id=' , ".$type."_id) AND (language_id = '".(int) $lang."' OR language_id = 0) LIMIT 1) AS seo_keyword";
      } else {
        $extra_select .= ",(SELECT keyword FROM " . DB_PREFIX . $this->url_alias . " WHERE query = CONCAT('".$type."_id=' , ".$type."_id) LIMIT 1) AS seo_keyword";
      }
      
			if($type == 'information') {
				$default_order = 'title';
			}
		} elseif (in_array($type, array('manufacturer'))) {
			$select_cols = "`".implode("`, `", self::pluck_fields($columns, 'db'))."`";
			$this->filter_language = false;
			$table = DB_PREFIX . $type . ' m';
			$default_order = 'name';
			$extra_select = ",(SELECT keyword FROM " . DB_PREFIX . $this->url_alias . " WHERE query = CONCAT('".$type."_id=' , m.".$type."_id) LIMIT 1) AS keyword";
		} elseif (in_array($type, array('common', 'special'))) {
			$select_cols = "`query`, `keyword`, `".$this->url_alias."_id`";
			$primaryKey = $this->url_alias.'_id';
			$table = DB_PREFIX . $this->url_alias;
			$default_order = 'query';
      if ($this->config->get('mlseo_ml_mode')) {
        $this->filter_language = $lang;
        if ($type == 'common') {
          $extra_where = "AND query LIKE 'route=%'";
        } elseif ($type == 'special') {
          $extra_where = "AND query NOT LIKE 'route=%'
                       AND query NOT LIKE 'product_id=%'
                       AND query NOT LIKE 'category_id=%'
                       AND query NOT LIKE 'information_id=%'
                       AND query NOT LIKE 'manufacturer_id=%'";
        }
      } else {
        $this->filter_language = false;
        if ($type == 'common') {
          $extra_where = "AND query LIKE 'route=%'";
        } elseif ($type == 'special') {
          $extra_where = "AND query NOT LIKE 'route=%'
                       AND query NOT LIKE 'product_id=%'
                       AND query NOT LIKE 'category_id=%'
                       AND query NOT LIKE 'information_id=%'
                       AND query NOT LIKE 'manufacturer_id=%'";
        }
			}
			$type = 'url_alias';
    } elseif ($type == 'absolute') {
			$select_cols = "`query`, `redirect`, `url_absolute_id`";
			$primaryKey = 'url_absolute_id';
			$table = DB_PREFIX . 'url_absolute';
			$default_order = 'query';
			$type = 'url_absolute';
      
      if ($this->config->get('mlseo_ml_mode')) {
        $this->filter_language = $lang;
      } else {
        $this->filter_language = false;
			}
		} elseif ($type == 'redirect') {
			$select_cols = "`query`, `redirect`, `url_redirect_id`";
      $this->filter_language = false;
			$primaryKey = 'url_redirect_id';
			$table = DB_PREFIX . 'url_redirect';
			$default_order = 'query';
			$type = 'url_redirect';
		} elseif ($type == '404') {
			$select_cols = "u.`query`, u.`count`, u.`url_404_id`, (r.query IS NOT NULL) AS has_redirect";
      $this->filter_language = false;
			$primaryKey = 'url_404_id';
			$table = DB_PREFIX . "url_404 u LEFT JOIN " . DB_PREFIX . "url_redirect r ON (u.query = r.query OR REPLACE(u.query, '".HTTP_CATALOG."', '/') = r.query)";
			$default_order = 'query';
			$type = 'url_404';
		} else {
			$this->filter_language = false;
			$table = DB_PREFIX . $type;
		}
		
		// Build the SQL query string from the request
		$limit = self::limit( $request, $columns );
		$order = self::order( $request, $columns, $default_order );
    
    if ($type == 'url_404') {
      $where = self::filter( $request, array(array('db' => 'u`.`query', 'dt' => 0)), $bindings );
    } else {
      $where = self::filter( $request, $columns, $bindings );
    }
		
    if (!$where) {
      $where = 'WHERE 1';
    }
		
		// Main query to actually get the data
		$data = $this->db->query("SELECT SQL_CALC_FOUND_ROWS ". $select_cols . "
			" . $extra_select  ."
			 FROM " . $table . ' '
			 . $extra_join . ' '
			 . $where . ' '
			 . $extra_where . ' '
			 . $order . ' '
			 . $limit)->rows;

		// Data set length after filtering
		$resFilterLength = $this->db->query("SELECT FOUND_ROWS() as `rows`")->row;
		
		$recordsFiltered = $resFilterLength['rows'];

		// Total data set length
		$resTotalLength =  $this->db->query(
			"SELECT COUNT(`{$primaryKey}`) AS `total`
			 FROM $table"
		)->row;
		
		$recordsTotal = $resTotalLength['total'];

		/*
		 * Output
		 */
		return array(
			"draw"            => intval( $request['draw'] ),
			"recordsTotal"    => intval( $recordsTotal ),
			"recordsFiltered" => intval( $recordsFiltered ),
			"data"            => self::data_output( $columns, $data, $type )
		);
	}


	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Internal methods
	 */

	/**
	 * Throw a fatal error.
	 *
	 * This writes out an error message in a JSON string which DataTables will
	 * see and show to the user in the browser.
	 *
	 * @param  string $msg Message to send to the client
	 */
	public function fatal ( $msg )
	{
		echo json_encode( array( 
			"error" => $msg
		) );

		exit(0);
	}

	/**
	 * Create a PDO binding key which can be used for escaping variables safely
	 * when executing a query with sql_exec()
	 *
	 * @param  array &$a    Array of bindings
	 * @param  *      $val  Value to bind
	 * @param  int    $type PDO field type
	 * @return string       Bound key to be used in the SQL where this parameter
	 *   would be used.
	 */
	public function bind ( &$a, $val, $type )
	{
		$key = ':binding_'.count( $a );

		$a[] = array(
			'key' => $key,
			'val' => $val,
			'type' => $type
		);

		return $key;
	}


	/**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	public function pluck ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
				$out[] = $a[$i][$prop];
		}

		return $out;
	}
  
  public function pluck_fields ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
			if($a[$i][$prop] != 'keyword' && $a[$i][$prop] != 'seo_keyword' && $a[$i][$prop] != 'related')
				$out[] = $a[$i][$prop];
		}

		return $out;
	}
}