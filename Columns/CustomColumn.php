<?php

namespace Component\DataGrid\Columns;

use Nette,
		Nette\Diagnostics\Debugger;

class CustomColumn extends Column
{
	/** @var callback	Callback to render HTML:el */
	public $html;
	
	
	/**
	 * @param string $name 
	 */
	public function __construct($parent, $name) {
		parent::__construct($parent, $name);

		$this->kind = 'custom';
	}
	
	
	
	/**
	 * Set callback to HTML:el()
	 * @param callback $html
	 * @return CustomColumn 
	 */
	public function setHtml($html) {
		$this->html = $html;
		return $this;
	}
	
	
	
	/**
	 * Call saved callback
	 * @param Nette\Database\Table\ActiveRow $row
	 * @return Nette\Utils\Html
	 */
	public function renderHtml($row) {		
		$html_el = call_user_func($this->html, $row);
		return $html_el;
	}
	
}
