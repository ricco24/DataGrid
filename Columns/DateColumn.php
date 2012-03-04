<?php

namespace Component\DataGrid\Columns;

use Nette,
		Nette\Diagnostics\Debugger;

class DateColumn extends Column
{
	/** @var string		Date format */
	public $date_format = "d.m.Y";
	
	
	
	/**
	 * @param string $name 
	 */
	public function __construct($parent, $name) {
		parent::__construct($parent, $name);

		$this->kind = 'date';
	}
	
	
	
	/**
	 * Set date format to render
	 * @param string $date_format
	 * @return DateColumn 
	 */
	public function setDateFormat($date_format) {
		$this->date_format = $date_format;
		return $this;
	}

	
}
