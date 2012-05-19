<?php

namespace Component\DataGrid\Columns;

use Nette,
		Nette\Utils\Strings,
		Nette\Diagnostics\Debugger;

class Column extends Nette\ComponentModel\Component 
{
	/** @var string	*/
	public $caption;
	
	/** @var string */
	public $kind;
	
	/** @var bool */
	public $filter = FALSE;
	
	/** @var string		Default filter state for column */
	public $default_filter;
	
	/** @var string		Full name of column eg. table.column - need for filtering joined (multi tables) sources */
	public $full_name;
	
	/** @var string		CSS styles in string */
	public $style;
	
	/** @var array		Select values - select filter variable */
	public $select;
	
	
	
	/**
	 * @param string $name 
	 */
	public function __construct($parent, $name) {
		parent::__construct();
		
		$this->caption = $name;
		$this->setParent($parent);
	}
		
	
	
	/**
	 * Set text filter on a column
	 * @param string $full_name
	 * @return Column 
	 */
	public function setTextFilter($full_name = NULL) {
		$this->filter = 'text';
		$this->full_name = $full_name;
		
		$this->parent->hasFilter = TRUE;
		
		return $this;
	}
	
	
	
	/**
	 * Set boolean filter on a column
	 * @param string $full_name
	 * @return Column 
	 */
	public function setBoolFilter($full_name = NULL) {
		$this->filter = 'bool';
		$this->full_name = $full_name;
		
		$this->parent->hasFilter = TRUE;
		
		return $this;
	}
	
	
	
	/**
	 * Set int filter on a column
	 * @param string $full_name
	 * @return Column 
	 */
	public function setIntFilter($full_name = NULL) {
		$this->filter = 'int';
		$this->full_name = $full_name;
		
		$this->parent->hasFilter = TRUE;
		
		return $this;
	}
	
	
	
	/**
	 * Set select filter on a column
	 * @param array $select
	 * @param string $full_name
	 * @return Column 
	 */
	public function setSelectFilter($select, $full_name = NULL) {
		$this->filter = 'select';
		$this->full_name = $full_name;
		$this->select = $select;
		
		$this->parent->hasFilter = TRUE;
		
		return $this;
	}
		
	
	
	/**
	 * Set date filter on a column
	 * @param string $full_name
	 * @return Column 
	 */
	public function setDateFilter($full_name = NULL) {
		$this->filter = 'date';
		$this->full_name = $full_name;
		
		$this->parent->hasFilter = TRUE;
		
		return $this;
	}
	
	
	
	/**
	 * Set default filter state for column
	 * @param stryng $filter
	 * @return Column 
	 */
	public function setDefaultFilter($filter) {
		if(!$this->filter) {
			throw new \Exception('This column havent got filter.');
		}		
		$this->default_filter = $filter;
		
		return $this;
	}
	
	
	
	/**
	 * Get default filter state for column
	 * @return string 
	 */
	public function getDefaultFilter() {
		return $this->default_filter;
	}
	
	
	
	/**
	 *
	 * @param string $style 
	 */
	public function setStyle($style) {
		$this->style = $style;	
		
		return $this;
	}	
}
