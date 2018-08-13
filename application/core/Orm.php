<?php defined('BASEPATH') or exit('No direct script access allowed');

	class Orm extends MY_Model 
	{
		public $softDelete;
		public $hidden = [];
		public $primaryKey;
		public $timeStamps;
		public $orderBy;
		
        public function __construct()
        {
            parent::__construct();
            $ormConfig = config_item('orm_config');
            $this->primaryKey = $ormConfig['primary_key'];
			$this->timeStamps = $ormConfig['time_stamps'];
			$this->orderBy = $ormConfig['order_by'];
			$this->softDelete = $ormConfig['soft_delete'];
			
			if ($this->softDelete['use'] == true) {
				array_push($this->hidden, $this->softDelete['column_name']);
			}
        }

		/**
		 * Get All Posts from the db limited by the $limit parameter
         * If the relationship parameter is passed the getWith() Function is called which joins
         * the models defined in each relationship to each corresponding row of the final results
		 * @param array
         * @param int
         * @param array
		 * @return array
		 */
		public function get(array $params = [], array $relationships = [], $limit = FALSE) 
		{
			if (property_exists($this, 'orderBy')) {
				$this->db->order_by($this->orderBy['column'], $this->tableName.'.'.$this->orderBy['order']);
			}
			else {
				$this->db->order_by($this->tableName.'.'.$this->primaryKey, 'DESC');
			}
			
			if ($this->softDelete['use'] == TRUE) {
				$params[$this->softDelete['column_name']] = NULL;
			}

			$results;
			if (count($relationships) > 0) {
				$results = $this->getWith($limit, $params, $relationships);
			}
			else {
				foreach ($params as $key => $value) {
					if (is_array($value)) {
						$paramCount = 0;
						foreach ($value as $index => $clauseValue) {
							if ($paramCount < 1) {
								$this->db->where($this->tableName.'.'.$key, $clauseValue);
							} 
							else {
								$this->db->or_where($this->tableName.'.'.$key, $clauseValue);
							}
							$paramCount++;
						}
					}
					else if (!stripos($key, '.')) {
						$this->db->where($this->tableName.'.'.$key, $value);
					}
					else {
						$this->db->where($this->tableName.'.'.$key, $value);
					}
				}
	
				$results = $this->db->get($this->tableName, $limit)->result_array();
			}
			foreach ($results as $result) {
				if (property_exists($this, 'hidden')) {
					foreach ($this->hidden as $hidden_attribute) {
						unset($result[$hidden_attribute]);
					}
				}
			}

			return $results;
		}

		/**
		 * Returns the First row matching the given parameters from the database
         * If the relationship parameter is passed the getWith() Function is called which joins
         * the models defined in each relationship to the row return
		 * @param array
         * @param array
		 * @return array
		 */
		public function getFirst(array $params, array $relationships = []) 
		{
			$this->db->order_by($this->primaryKey, 'DESC');
			if ($this->softDelete['use'] === TRUE) {
				$params[$this->softDelete['column_name']] = NULL;
			}
			$result;
			if (count($relationships) > 0) {
				$result = $this->getWith(FALSE, $params, $relationships);
				$result = reset($result);
			}
			else {
				foreach ($params as $key => $value) {
					if (!stripos($key, '.')) {
						$this->db->where($this->tableName.'.'.$key, $value);
					}
				}
				$result = $this->db->get_where($this->tableName, $params)->row_array();
			}
			if ($result != NULL) {
				if (property_exists($this, 'hidden')) {
					foreach ($this->hidden as $hidden_attribute) {
						unset($result[$hidden_attribute]);
					}
				}
			}
			return $result;
		}

        /**
         * Get the rows that match the given parameters and fetch the spemodelfied relationships 
         * as attributes of each row returned
         * @param int $limit: How many recorde should be returned fro database
         * @param array $params: Which records should be returned
         * @param array $relationships: associations as defined in Model
         * @return array
         */
		private function getWith($limit = FALSE, array $params = [], array $relationships, $side = 'LEFT')
		{
			$select_query = $this->tableName.'.*, ';
			$joins = [];
			$relationship_options;
			foreach ($relationships as $relationship) {
				if(method_exists($this, $relationship)) {
					$relationship_options[$relationship] = call_user_func([$this, $relationship]);
					$select_query = $select_query.$relationship_options[$relationship]['appended_columns'];
					if ($relationship_options[$relationship]['type'] == 'belongs_to_many') {
						foreach ($relationship_options[$relationship]['join'] as $joiner) {
							array_push($joins, $joiner);
						}
					}
					else {
						array_push($joins, $relationship_options[$relationship]['join']);
					}
				}
				else {
					$breakup = strtoupper($relationship);
					show_error("Relationship $breakup does not exist on this model", 500);
				}
			}

			if (property_exists($this, 'orderBy')) {
				$this->db->order_by($this->orderBy['column'], $this->tableName.'.'.$this->orderBy['order']);
			}
			else {
				$this->db->order_by($this->tableName.'.'.$this->primaryKey, 'DESC');
			}

			$this->db->select($select_query, FALSE);

			foreach ($joins as $join) {
				$this->db->join($join['related_table'], $join['relationship'], $side);
			}

			if ($this->softDelete['use'] === true) {
				$params[$this->softDelete['column_name']] = NULL;
			}

			foreach ($params as $key => $value) {
				if (is_array($value)) {
					$paramCount = 0;
					foreach ($value as $index => $clauseValue) {
						if ($paramCount < 1) {
							$this->db->where($this->tableName.'.'.$key, $clauseValue);
						} 
						else {
							$this->db->or_where($this->tableName.'.'.$key, $clauseValue);
						}
						$paramCount++;
					}
				}
				else if (!stripos($key, '.')) {
					$this->db->where($this->tableName.'.'.$key, $value);
				}
				else {
					$this->db->where($this->tableName.'.'.$key, $value);
				}
			}

			$result_array = $this->db->get($this->tableName, $limit)->result_array();

			$result_array = $this->sortResult($result_array, $relationships, $relationship_options);
			return $result_array;
			
		}

        /**
         * Sort results from getWith into an array of assomodelative arrays containing the associations
         * for each unique record returned from the database
         * @param array $result_array: results from database
         * @param array $relationships: relationships passed into the the getWith() function
         * @param array $relationship_options: assomodelative array containing all data about each relationship
         * @return array
         */
		private function sortResult ($result_array, $relationships, $relationship_options) 
		{
			$primary_keys = [];
			$final_results = [];
			$searcher = 0;

			foreach ($result_array as $row) {
				$attributes;
				$unique = true;

				if (array_key_exists($row[$this->primaryKey], $final_results)) {
					$unique = FALSE;
				}

				if ($unique === true) 
				{
					foreach ($relationships as $relationship) {
						$i = 0;
						foreach ($row as $key => $value) {
							if ((strpos($key, $relationship_options[$relationship]['related_table_name'], 0) !== false)) 
							{
								$relationship_column_name = substr($key, (strlen($relationship_options[$relationship]['related_table_name']))+1);

								if ($relationship_column_name != 'deleted_at') 
								{
									if ($relationship_options[$relationship]['type'] != 'belongs_to') 
									{
										$attributes[$relationship][$i][$relationship_column_name] = $value;	
									}
									else 
									{
										$attributes[$relationship][$relationship_column_name] = $value;
									}
									unset($row[$key]);
								}
	
								// unset($row[$key]);
							}
						}
						$i++;	
					}
					$attributes = $this->unsetNulls($attributes, $relationship_options);

					$row = array_merge($row, $attributes);
					
					$final_results[$row[$this->primaryKey]] = $row;
				}
				else 
				{
					foreach ($relationships as $relationship) 
					{
						$i = 0;
						foreach ($row as $key => $value) 
						{
							if ((strpos($key, $relationship_options[$relationship]['related_table_name'], 0) !== false)) 
							{
								$relationship_column_name = substr($key, (strlen($relationship_options[$relationship]['related_table_name']))+1);

								if ($relationship_options[$relationship]['type'] != 'belongs_to') 
								{
									$attributes[$relationship][$i][$relationship_column_name] = $value;	
								}
								else 
								{
									$attributes[$relationship][$relationship_column_name] = $value;
								}
								unset($row[$key]);
							}
						}
						$attributes = $this->unsetNulls($attributes, $relationship_options);
						if (count($attributes[$relationship]) && $relationship_options[$relationship]['type'] != 'belongs_to') 
						{
							$new_index = count($final_results[$row[$this->primaryKey]][$relationship]);
							$final_results[$row[$this->primaryKey]][$relationship][$new_index] = $attributes[$relationship][0];
							$i++;
						}
					}
				}
			}
			return array_values($final_results);
		}

		private function unsetNulls($attributes, $relationship_options)
		{
			foreach ($attributes as $relationship => $attribute) {
				if ($relationship_options[$relationship]['type'] == 'has_many' || $relationship_options[$relationship]['type'] == 'belongs_to_many') {
					foreach ($attribute as $attribute_key => $atrribute_value) {
						$row_count = count($atrribute_value);
						$i = 0;
					   foreach ($atrribute_value as $key => $value) {
						   if ($value == null) {
							   $i++;
						   }
					   }
					   if ($i >= $row_count) {
						   unset($attribute[$attribute_key]);
					   }
					}
					$attributes[$relationship] = $attribute;
				}
				else {
				// 	$row_count = count($attribute);
				// 	$i = 0;
				//    foreach ($attribute as $key => $value) {
				// 	   if ($value == null) {
				// 		   $i++;
				// 	   }
				//    }
				//    if ($i >= $row_count) {
				// 	$attributes[$relationship] = null;
				//    }
				}
			}
			return $attributes;
		}
		
		/**
		 * Insert a new record into the database and return the inserted record
         * @param array $values: assomodelative array of the row to be inserted
         * @param array $returnWith: parameters by which to fetch the inserted row
		 * @return array
         * @return bool
		 */
		public function create (array $values, $return = true) 
		{
			if (property_exists($this, 'timeStamps')) {
				if (isset($this->timeStamps['create']) && $this->timeStamps['use'] == true) {
					$values[$this->timeStamps['create']] = $this->currentDateTime;
				}

				if (isset($this->timeStamps['update']) && $this->timeStamps['use'] == true) {
					$values[$this->timeStamps['update']] = $this->currentDateTime;
				}
			}

			$attributes = $this->db->list_fields($this->tableName);

			foreach ($values as $key => $value) {
				if (!in_array($key, $attributes)) {
					unset($values[$key]);
				}
			}
			
			$this->db->insert($this->tableName, $values);
			$insert_id = $this->db->insert_id();

			if ($insert_id) {
				if ($return) {
					return $this->getFirst([$this->primaryKey => $insert_id]);
				}
				else {
					return true;
				}
			}
			else {
				return false;
			}
		}

		/**
		 * Insert a new record into the db if the params are not found
		 * @param array $params: Parameters to search the table with
		 * @param array $values: Values to insert if the record is not found
		 * @param bool $return: Whether or not to return the inserted row
		 * @param string $tableName: Name of table to search and insert if not found. Set to table name in current instance by default
		 */
		public function findOrCreate (array $params, array $values = [], $return = true, $tableName = null)
		{
			if ($tableName != null) {
				$this->tableName = $tableName;
			}

			$this->softDelete['use'] = FALSE;
			
			$row = $this->getFirst($params);
			
			if (empty($row)) {
				$values = array_unique(array_merge($params, $values), SORT_REGULAR);
				$row = $this->create($values, $return);
				return [
					'first' => true,
					'row' => $row
				];
			}
			else {
				return [
					'first' => false,
					'row' => $row
				];
			}
		}

		/**
		 * Update a record that matches the parameters with the values passsed
		 * @param array $params
         * @param array $values
		 * @return string
		 */
		public function update (array $params, array $values) 
		{
			if ($this->softDelete['use'] === TRUE) {
				$params[$this->softDelete['column_name']] = NULL;
			}


			foreach ($values as $key => $value) 
			{
				if (property_exists($this, 'fixed')) 
				{
					if (in_array($key, $this->fixed)) 
					{
						unset($values[$key]);
					}
				}

				if ($value == null && $value !== 0) 
				{
					unset($values[$key]);
				}

				if ($key === $this->primaryKey) {
					unset($values[$key]);
				}
			}

			if (property_exists($this, 'timeStamps')) {
				if (isset($this->timeStamps['update']) && $this->timeStamps['use'] == true) {
					$values[$this->timeStamps['update']] = $this->currentDateTime;
				}
			}

			$updated = $this->db->where($params)->update($this->tableName, $values);

			if ($updated) 
			{
				return $this->getFirst($params);
			}
			else 
			{
				return FALSE;
			}
		}

		/**
		 * Sort an array of results by a given key
		 * @param array $results: Array to be sorted
		 * @param string $key: $key to sort array with
		 * @param string $order
		 * @return array
		 */
		public function orderBy (array $array, $sort_key, $order = 'asc')
		{
			$sorter = [];
			foreach ($array as $key => $row) {
				$sorter[$key] = $row[$sort_key];
			}
			$order = strtolower($order);
			if ($order == 'desc') {
				array_multisort($sorter, SORT_DESC, $array);
			}
			else {
				array_multisort($sorter, SORT_ASC, $array);
			}
			return $array;
		}

		/**
		 * Delete record with given $parameters from database
		 * @param array $params
		 * @return bool
		 */
		public function delete (array $params) 
		{
			if ($this->softDelete['use'] === TRUE) {
				$this->update($params, [
					$this->softDelete['column_name'] => $this->currentDateTime
				]);
			}
			else {
				$this->db->where($params)->delete($this->tableName);
			}
			return true;
		}

        /**
         * This association is used to create a relationship between models whereby the current model
         * is owned by the related model
         * @param string $related_model
         * @param string $foreign_key_column_name: This is the foreign key that links to the related model
         * @return array
         */
		public function belongsTo ($related_model, $foreign_key_column_name) 
		{
			$this->load->model($related_model);
			$related_table_name = $this->$related_model->tableName;

			$attributes = $this->db->list_fields($related_table_name);
			
			if (property_exists($this->$related_model, 'hidden')) 
			{
				$hidden_attributes = $this->$related_model->hidden;
			
				foreach ($hidden_attributes as $hidden_attribute) 
				{
					if (($hiddenFieldIndex = array_search($hidden_attribute, $attributes)) !== false) {
						unset($attributes[$hiddenFieldIndex]);
					}
				}
			}

			$appended_columns = '';

			foreach ($attributes as $attribute) {
				$append_this = $related_table_name.'.'.$attribute. ' AS '. $related_table_name. '_'. $attribute.', ';
				$appended_columns = $appended_columns.$append_this;
			}

			$join['related_table'] = $related_table_name;
			$join['relationship'] = $this->tableName.'.'.$foreign_key_column_name.' = '.$related_table_name.'.'.$this->$related_model->primaryKey;
			return [
				'appended_columns' => $appended_columns,
				'join' => $join,
				'related_table_name' => $related_table_name,
				'type' => 'belongs_to'
			];
		}

        /**
         * This association is used to create a relationship between models whereby the current model
         * owns the related model
         * @param string $related_model
         * @param string $foreign_key_column_name: This is the foreign key that links to the related model
         * @return array
         */
		public  function hasMany ($related_model, $foreign_key_column_name)
		{
			$this->load->model($related_model);
			$related_table_name = $this->$related_model->tableName;

			$attributes = $this->db->list_fields($related_table_name);

			if (property_exists($this->$related_model, 'hidden')) 
			{
				$hidden_attributes = $this->$related_model->hidden;
			
				foreach ($hidden_attributes as $hidden_attribute) 
				{
					if (($hiddenFieldIndex = array_search($hidden_attribute, $attributes)) !== false) {
						unset($attributes[$hiddenFieldIndex]);
					}
				}
			}

			$appended_columns = '';

			foreach ($attributes as $attribute) 
			{
				$append_this = $related_table_name.'.'.$attribute. ' AS '. $related_table_name. '_'. $attribute.', ';
				$appended_columns = $appended_columns.$append_this;
			}

			$join['related_table'] = $related_table_name;
			$join['relationship'] = $this->tableName.'.'.$this->primaryKey.' = '.$related_table_name.'.'.$foreign_key_column_name;
			return [
				'appended_columns' => $appended_columns,
				'join' => $join,
				'related_table_name' => $related_table_name,
				'type' => 'has_many'
			];
		}

		public function belongsToTable ($related_table_name, $foreign_key_column_name, $primaryKey) 
		{
			// $this->load->model($related_model);
			// $related_table_name = $this->$related_model->tableName;

			$attributes = $this->db->list_fields($related_table_name);
			
			// if (property_exists($this->$related_model, 'hidden')) 
			// {
			// 	$hidden_attributes = $this->$related_model->hidden;
			
			// 	foreach ($hidden_attributes as $hidden_attribute) 
			// 	{
			// 		unset($attributes[$hidden_attribute]);
			// 	}
			// }

			$appended_columns = '';

			foreach ($attributes as $attribute) {
				$append_this = $related_table_name.'.'.$attribute. ' AS '. $related_table_name. '_'. $attribute.', ';
				$appended_columns = $appended_columns.$append_this;
			}

			$join['related_table'] = $related_table_name;
			$join['relationship'] = $this->tableName.'.'.$foreign_key_column_name.' = '.$related_table_name.'.'.$primaryKey;
			return [
				'appended_columns' => $appended_columns,
				'join' => $join,
				'related_table_name' => $related_table_name,
				'type' => 'belongs_to'
			];
		}

        /**
         * This association is used to create a relationship between the current model
         * and a table in the database whereby it owns several rows in the related table
         * @param string $property_table
         * @param string $foreign_key_column_name: This is the foreign key that links to the related model
         * @return array
         */
		public function hasManyTable ($related_table_name, $foreign_key_column_name)
		{
			// $this->load->model($related_model);
			$related_table_name;

			$attributes = $this->db->list_fields($related_table_name);

			// if (property_exists($this->$related_model, 'hidden'))
			// {
			// 	$hidden_attributes = $this->$related_model->hidden;
			
			// 	foreach ($hidden_attributes as $hidden_attribute) 
			// 	{
			// 		unset($attributes[$hidden_attribute]);
			// 	}
			// }

			$appended_columns = '';

			foreach ($attributes as $attribute) 
			{
				$append_this = $related_table_name.'.'.$attribute. ' AS '. $related_table_name. '_'. $attribute.', ';
				$appended_columns = $appended_columns.$append_this;
			}

			$join['related_table'] = $related_table_name;
			$join['relationship'] = $this->tableName.'.'.$this->primaryKey.' = '.$related_table_name.'.'.$foreign_key_column_name;
			return [
				'appended_columns' => $appended_columns,
				'join' => $join,
				'related_table_name' => $related_table_name,
				'type' => 'has_many'
			];
		}

        /**
         * This association is used to create a relationship between models whereby the current model
         * is owned by more than one row of the related model.
         * @param string $related_model
         * @param string $pivot_table: The table that stores each association between records
         * @param string $model_key: The column name that represents the current model on the pivot table
         * @param string $foreign_key: The column name that represents the related model on the pivot table
         * @return array
         */
		public function belongsToMany ($related_model, $pivot_table, $model_key, $foreign_key)
        {
            $this->load->model($related_model);
			$related_table_name = $this->$related_model->tableName;

			$attributes = $this->db->list_fields($related_table_name);
			
			if (property_exists($this->$related_model, 'hidden')) 
			{
				$hidden_attributes = $this->$related_model->hidden;
			
				foreach ($hidden_attributes as $hidden_attribute) 
				{
					if (($hiddenFieldIndex = array_search($hidden_attribute, $attributes)) !== false) {
						unset($attributes[$hiddenFieldIndex]);
					}
				}
			}
            
            $appended_columns = '';

			foreach ($attributes as $attribute) 
			{
				$append_this = $related_table_name.'.'.$attribute. ' AS '. $related_table_name. '_'. $attribute.', ';
				$appended_columns = $appended_columns.$append_this;
            }
            
			$join[0]['related_table'] = $pivot_table;
            $join[0]['relationship'] = $this->tableName.'.'.$this->primaryKey.' = '.$pivot_table.'.'.$model_key;

            $join[1]['related_table'] = $related_table_name;
            $join[1]['relationship'] = $pivot_table.'.'.$foreign_key.' = '.$related_table_name.'.'.$this->$related_model->primaryKey;

			return [
				'appended_columns' => $appended_columns,
				'join' => $join,
                'related_table_name' => $related_table_name,
				'type' => 'belongs_to_many'
			];
        }
	}

