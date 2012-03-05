<?php

/**
 * @author Samuel Kelemen
 * @copyright Copyright (c) 2012 Samuel Kelemen
 */

namespace Component\DataGrid;

use Nette,
		Nette\Application\UI\Form,
		Nette\Utils\Strings,
		Nette\Utils\Paginator,
		Nette\Environment,
		Nette\Diagnostics\Debugger;

class DataGrid extends Nette\Application\UI\Control
{	
	/** @var Nette\Database\Table\Selection		Notorm datasource */ 
	private $dataSource;
	
	/** @var array	Order column and direction (persist) */
	public $order;
	
	/** @var array	Column -> like (persist) */
	public $filter;
	
	/** @var Nette\Utils\Paginator */
	private $paginator;
	
	/** @var int	Paginator page (persist) */
	public $page = '1';
	
	/** @var int */
	private $itemsPerPage = '5';
	
	/** @var array	Numbers of items per page */
	private $itemsPerPageDropdown = array('all', '5', '10', '15', '20', '50');
	
	/** @var array	Array of Column objects */
	private $th = array();
	
	/** @var array	Array of actions on each row */
	private $actions;
	
	/** @var array	Array of global actions */
	private $global_actions;
	
	/** @var array	Array of data to render (row data, row actions)*/
	private $data;
	
	/** @var Nette\Http\SessionSection */
	public $gridSession;
	
	/** @var boolean	Has DataGrid a filter */
	public $hasFilter = FALSE;
	
	
	
	/**
	 * Set new datasource
	 * @param Nette\Database\Table\Selection $dataSource 
	 */
	public function setDataSource(Nette\Database\Table\Selection $dataSource) {
		$this->dataSource = new DataSource($dataSource);
	}
	
	
	
	/**
	 * Get datasource
	 * @return Nette\Database\Table\Selection 
	 */
	public function getDataSource() {
		return $this->dataSource;		
	}
	
	
	
	// ******************** public setters ********************************
	
	
	
	/**
	 * Set items per page - select item
	 * @param array $dropdown 
	 */
	public function setItemsPerPageDropdown(array $dropdown) {
		$this->itemsPerPageDropdow = $dropdown;
	}
	
	
	
	/**
	 * Set items per page ( paginator )
	 * @param int $items_per_page 
	 */
	public function setItemsPerPage($items_per_page) {
		$this->itemsPerPage = (int) $items_per_page;
	}
	
	
	
	/**
	 * Function add a new text column to dataGrid
	 * @param string $column_name	Column name in datasource
	 * @param string $caption		Render column name
	 * @return Column\TextColumn 
	 */
	public function addColumn($column_name, $caption) {
		
		if(in_array($column_name, $this->dataSource->exist_columns)) {
			if(!array_key_exists($column_name, $this->th)) {
				$this->th[$column_name] = new Columns\TextColumn($this, $caption);
			} else {
				throw new \Exception("Two columns have the same source name - *$column_name*");
			}
		} else {
			throw new \Exception("Datasource not contain a column named *$column_name*");
		}
		
		return $this->th[$column_name];
	}
	
	
	
	/**
	 * Function add a new text column to dataGrid
	 * @param string $column_name	Column name in datasource
	 * @param string $caption		Render column name
	 * @return Column\BoolColumn 
	 */
	public function addBoolColumn($column_name, $caption) {
		if(in_array($column_name, $this->dataSource->exist_columns)) {
			if(!array_key_exists($column_name, $this->th)) {
				$this->th[$column_name] = new Columns\BoolColumn($this, $caption);
			} else {
				throw new \Exception("Two columns have the same source name - *$column_name*");
			}
		} else {
			throw new \Exception("Datasource not contain a column named *$column_name*");
		}
		
		return $this->th[$column_name];
	}
	
	
	
	/**
	 * Function add a new date column to dataGrid
	 * @param string $column_name	Column name in datasource
	 * @param string $caption		Render column name	
	 * @return Column\DateColumn	
	 */
	public function addDateColumn($column_name, $caption) {
		if(in_array($column_name, $this->dataSource->exist_columns)) {
			if(!array_key_exists($column_name, $this->th)) {
				$this->th[$column_name] = new Columns\DateColumn($this, $caption);
			} else {
				throw new \Exception("Two columns have the same source name - *$column_name*");
			}
		} else {
			throw new \Exception("Datasource not contain a column named *$column_name*");
		}
		
		return $this->th[$column_name];
	}
	
	
	
	/**
	 * Function add a new custom column to dataGrid
	 * The name of this column don't have to be in datasource
	 * @param type $column_name		Column name in datasource
	 * @param type $caption			Render column name
	 * @return Column\CustomColumn
	 */
	public function addCustomColumn($column_name, $caption) {
		if(!array_key_exists($column_name, $this->th)) {
			$this->th[$column_name] = new Columns\CustomColumn($this, $caption);
		} else {
			throw new \Exception("Two columns have the same source name - *$column_name*");
		}
		
		return $this->th[$column_name];
	}
		
	
	
	/**
	 * Add new action to each row of datagrid
	 * @param string $action_name		Action name - action icon
	 * @param string $title				Title of anchor
	 * @param string $redirect			Redirect to - nette format eg. Homepage:addPage
	 * @param array $data				Data 
	 */
	public function addAction($action_name, $title, $redirect, $data = NULL) {
		$this->actions[$action_name] = array(
				'title' => $title,
				'redirect' => $redirect
			); 
		if($data) {
			$this->actions[$action_name]['data'] = $data; 
		}
	}
		
	
	
	/**
	 * Add new global action to datagrid
	 * @param type $action_name			Action name - action icon
	 * @param type $title				Title of anchor
	 * @param type $redirect			Redirect to - nette format eg. Homepage:addPage
	 * @param type $data				Data
	 */
	public function addGlobalAction($action_name, $title, $redirect, $data = NULL) {
		$this->global_actions[$action_name] = array(
				'title' => $title,
				'redirect' => $redirect
			); 
		
		if($data) {
			$this->global_actions[$action_name]['data'] = $data; 
		}
	}
	
	
	//**************************** Filtering ***********************************
	

	
	/**
	 * Get formated data array (columns, actions)
	 * @return array	Primary data array	 
	 */
	public function getData() {
		
		$this->setfilter();
		$this->setOrder();			
		$this->setPaginator();
		$this->setLimit();
		
		return $this->dataSource->getData($this->th, $this->actions);
	}
	
	
	
	/**
	 * Apply ordering
	 */
	public function setOrder() {		
		if(!empty($this->order)) {
			$this->dataSource->sortData($this->order);
		}
	}
	
	
	
	/**
	 * Apply filters
	 */
	public function setFilter() {
		$filter_table = $this->getFilterTable();
		if(!empty($this->filter)) {
			$this->dataSource->filterData($this->filter, $filter_table);
		}
	}
	
	
	
	/**
	 * Help function to get filter table
	 * @return mixin
	 */
	public function getFilterTable() {
		foreach($this->th as $key => $column) {
			if($column->full_name) {
				$filter_table[$key] = $column->full_name;
			}
		}
		
		return isset($filter_table) ? $filter_table : NULL;
	}
	
	
	
	/**
	 * Apply limit
	 */
	public function setLimit() {
		if(!empty($this->paginator)) {
			$this->dataSource->setLimit($this->paginator->getLength(), $this->paginator->getOffset());
		}
	}
	
	
	
	/**
	 * Paginator setter
	 */
	private function setPaginator() {
		
		$this->paginator = new Paginator();

		$this->paginator->itemCount = $this->dataSource->getCount();
		$this->paginator->page = $this->page;
		
		$value = (int) $this->itemsPerPage;
		if($value > 0) {
			$this->paginator->itemsPerPage = $this->itemsPerPage;
		} else {
			$this->paginator->itemsPerPage = $this->dataSource->getCount();
		}
	}
	
	
	
	//************************** Forms *****************************************
	
	
	
	/**
	 * DataGrit form
	 * @param Form $form
	 * @return Form 
	 */
	public function createComponentForm() {
		$form = new Form();
		
		//create filter form elements
		foreach($this->th as $key => $value) {
			if($value->filter) {
				switch($value->filter) {
					case 'text':
						$form->addText($key)
							->getControlPrototype()->class('filter-' . $value->filter);
						break;
					case 'date':
						$form->addText($key)
							->setHtmlId($this->name . '-' .$key . '-date-filter')
							->getControlPrototype()->class('filter-' . $value->filter);
						break;					
					case 'int':
						$form->addText($key)
							->getControlPrototype()->class('filter-' . $value->filter);;
						break;
					case 'select':
						$form->addSelect($key, NULL, $value->select)
								->getControlPrototype()->class('filter-' . $value->filter);;
						break;
					case 'bool':
						$form->addSelect($key, '', array('yes' => 'Áno','no' => 'Nie'))
							->setPrompt('')
							->getControlPrototype()->class('filter-' . $value->filter);
						break;
				}
			}
		}
		
		$form->addSubmit('filter', 'Filtrovať')
				->getControlPrototype()->class('grid-submit-filter');
		
		$self = $this;
		$th = $this->th;
		$form->onSuccess[] = function($form) use ($self, $th){			
			$set_values = array();
			foreach($form->values as $key => $value) {				
				$value = Strings::normalize($value);
				if(!empty($value)) {
					$set_values[$key] = array(
						'value' => $th[$key]->filter == 'bool' ? $self->stringToBool($value) : $value,
						'kind' => $th[$key]->filter,
					);
				}
			}

			$self->filter = $set_values;
			$self->page = '1';
			$self->finalize();
		};
		
		return $form;
	}
	
	
	
	/**
	 * Paginator form
	 * @param Form $form
	 * @return Form 
	 */
	public function createComponentPagingForm() {
		$form = new Form();
		
		$form->addText('page', '');
		
		$self = $this;
		$form->onSuccess[] = function($form) use ($self) {
			$self->handlePage($form->values->page);
		};
		
		return $form;
	}
	
	
	
	/**
	 * Dropdown form - select items per page
	 * @param Form $form
	 * @return Form 
	 */
	public function createComponentDropdownForm() {
		$form = new Form();
		
		$form->addSelect('itemsPerPage', '', array_combine($this->itemsPerPageDropdown, $this->itemsPerPageDropdown))
				->getControlPrototype()->onchange("submit();");
		
		$self = $this;
		$form->onSuccess[] = function($form) use ($self) {
			$self->itemsPerPage = $form->values->itemsPerPage;
			
			if(isSet($self->filter)) {
				foreach($self->filter as $key => $value) { 
					if($value['kind'] == 'bool') {
						$self->filter[$key]['value'] = $self->stringToBool($value['value']);
					}			
				}
			}
			
			$self->page = '1';			
			$self->finalize();
		};
		
		return $form;
	}
	
	
	
	//******************************* Render ***********************************


	
	/**
	 * Render function
	 */
	public function render() {
		$this->template->setFile(__DIR__ . '/Templates/dataGrid.latte');
		
		$filter_form = $this['form'];
		$paging_form = $this['pagingForm'];
		$dropdown_form = $this['dropdownForm'];

		$paging_form->setDefaults(array( 
						'page' => $this->page
					));
		
		$dropdown_form->setDefaults(array(
						'itemsPerPage' => $this->itemsPerPage
					));
		
		$this->template->form = $filter_form;
		$this->template->pagingForm = $paging_form;
		$this->template->dropdownForm = $dropdown_form;
		$this->template->columns = $this->th;
		$this->template->actions = $this->actions;
		$this->template->global_actions = $this->global_actions;
		$this->template->data = $this->getData();;		
		$this->template->paginator = $this->paginator;
		$this->template->gridName = $this->getName();
		$this->template->hasFilter = $this->hasFilter;
		
		//set defaults to filter form
		if(isSet($this->filter)) {
			$defaults = array();
			foreach($this->filter as $key => $value) { 
				if($value['kind'] == 'bool') {
					$this->filter[$key]['value'] = $this->boolToString($value['value']);
				}
				$defaults[$key] = $this->filter[$key]['value'];
			}
			$filter_form->setDefaults($defaults);
		}
		
		$this->saveSession();		
		$this->template->render();
	}
	
	
	//************************* Handle signals *********************************
	
	
	
	/**
	 * Finalize function - finish save state before redirect
	 */
	public function finalize() {
		$presenter = $this->getPresenter();
		$this->saveSession();
		$presenter->redirect('this');	
	}
	
	
	
	/**
	 * Handle column ordering
	 * @param string $column 
	 */
	public function handleOrder($column = NULL) {
		$this->order['column'] = $column;
		if(isSet($this->order['way'])) {
			$this->order['way'] = $this->order['way'] == 'ASC' ? 'DESC' : 'ASC';
		} else {
			$this->order['way'] = 'ASC';
		}

		$this->finalize();
	}
	
	
	
	/**
	 * Handle paginator
	 * @param int $page 
	 */
	public function handlePage($page) {
		$page = (int) $page;
		$this->page = ($page < 0) ? '1' : $page;
		
		$this->finalize();
	}
	
	
	
	/**
	 * Handle reset grid state
	 */
	public function handleReset() {
		$this->page = '1';
		$this->filter = NULL;
		$this->order = NULL;
		
		$this->finalize();
	}
	
	
	//********************** State functions ***********************************
	
	/**
	 * Save state function (TODO)
	 */
	public function saveState(array & $params)
	{
		parent::saveState($params);
	}
	
	
	
	/**
	 * Get and load session params
	 * @param array $params 
	 */
	public function loadState(array $params)
	{
		parent::loadState($params);
		
		$this->getSession();		
		$this->loadSession();
	}
	
	
	
	/**
	 * Get session - unique for each component
	 */
	public function getSession() {
		$this->gridSession =  Environment::getSession('Component/Grid/' . $this->name);
	}
	
	
	
	/**
	 * Load all params from session variable
	 */
	public function loadSession() {
		$this->page = isset($this->gridSession->page) ? $this->gridSession->page : $this->page;
		$this->order = $this->gridSession->order;
		$this->filter = $this->gridSession->filter;
		$this->itemsPerPage = isset($this->gridSession->itemsPerPage) ? $this->gridSession->itemsPerPage : $this->itemsPerPage;
	}
	
	
	
	/**
	 * Save all params to session variable
	 */
	public function saveSession() {
		$this->gridSession->page = $this->page;
		$this->gridSession->order = $this->order;
		$this->gridSession->filter = $this->filter;
		$this->gridSession->itemsPerPage = $this->itemsPerPage;
	}	
	
	
	
	//*************************** Helping functions ****************************
	
	
	
	/**
	 * Convert string (enum yes, no) to boolean
	 * @param string $var
	 * @return boolean
	 */
	public function stringToBool($var) {
		$result = $var == 'yes' ? true : false;
		return $result;
	}
	
	
	
	/**
	 * Convert boolean to string enum (yes, no)
	 * @param boolean $var
	 * @return string
	 */
	public function boolToString($var) {
		$result = $var ? 'yes' : 'no';
		return $result;
	}	
}
