<?php

namespace Component\DataGrid;

use Nette,
		Nette\Utils\Html,
		Nette\Diagnostics\Debugger;

class DataSource extends Nette\Object
{
	/** @var Nette\Database\Table\Selection		Notorm datasource */
	private $notorm_source;
	
	/** @var array		Names of existing columns in datasource */
	public $exist_columns;
	
	
	
	/**
	 * @param Nette\Database\Table\Selection $source 
	 */
	public function __construct($source) {
		$this->notorm_source = $source;
		$this->getExistColumns();
	}
	
	
	
	/**
	 * Find existing columns in datasource
	 */
	public function getExistColumns() {
		foreach($this->notorm_source as $row) {
			$result = array_keys(iterator_to_array($row)); 
			break;
		}
		
		$this->exist_columns = $result;
	}
	
	
	
	/**
	 * Get data to render - columns, rows and actions
	 * @param array $columns	Array of columns objects
	 * @param array $actions	Array of actions
	 * @return array
	 */
	public function getData($columns, $actions = NULL) {
		$data_array = array();
		foreach($this->notorm_source as $row) {
			foreach($columns as $key => $value) {
				
				//set column kind
				$data['columns'][$key]['kind'] = $value->kind;
				
				//set columns data
				switch($value->kind) {
					case 'text':
						$data['columns'][$key]['value'] = $row->$key;
						break;
					case 'bool':					
						$data['columns'][$key]['value'] = $row->$key ? 'Áno' : 'Nie';
						break;
					case 'custom':
						$data['columns'][$key]['value'] = $value->renderHtml($row);
						break;
					case 'date':
						$data['columns'][$key]['value'] = $row->$key;
						$data['columns'][$key]['date_format'] = $value->date_format;
						break;
					default:
						$data['columns'][$key]['value'] = NULL;
						break;
				}
			
				//set actions data
				if(isSet($actions)) {
					foreach($actions as $action_key => $action) {
						$data['actions'][$action_key]['title'] = $action['title'];
						$data['actions'][$action_key]['redirect'] = $action['redirect'];
						
						if(isset($action['data'])){
							foreach($action['data'] as $action_data) {
								if($action_data == $key) {								
									$data['actions'][$action_key]['params'][$action_data] = $row->$key;								
								}
							}
						}
					}
				}			
			}
			
			$data_array[] = $data;
			//need unset -- else have problem with creating action params strings
			unset($data);
		}
		return $data_array;
	}
	
	
	
	/**
	 * Sorting function
	 * @param array $order 
	 */
	public function sortData(array $order) {
		$column = $order['column'];
		$way = $order['way'];
		
		$way = $way == 'ASC' ? ' DESC' : ' ASC';
		$this->notorm_source->order($column . $way);
	}
	
	
	
	/**
	 * Filter funciton
	 * @param array $filter_data	
	 * @param array $filter_table 
	 */
	public function filterData($filter_data, $filter_table) {
		try {
			foreach($filter_data as $key => $filter) {
				$field = $filter_table ? $filter_table[$key] : $key ;
				$value = $filter['value'];
				
				switch($filter['kind']) {
					case 'date':
					case 'text':
						$this->notorm_source->where("$field LIKE ?", "%$value%");
						break;
					case 'int':
						$values = explode(' ', $value);
						$allowed_operations = array('=', '>', '<', '>=', '<=', '<>');
						if(in_array($values[0], $allowed_operations)) {
							$this->notorm_source->where("$field $values[0] ?", $values[1]);
						} else {
							$this->notorm_source->where($field, $values[0]);
						}
						break;
					case 'select':
					case 'bool':
						$this->notorm_source->where($field, $value);
						break;
				}
			}
		} catch(\PDOException $e) {
			
		}
		
	}
	
	
	
	/**
	 * Set limit to datasource
	 * @param int $limit
	 * @param int $offset 
	 */
	public function setLimit($limit, $offset) {
		$this->notorm_source->limit($limit, $offset);
	}

	
	
	/**
	 * Count datasource rows
	 * @return int 
	 */
	public function getCount() {
		return $this->notorm_source->count('*');
	}
}
