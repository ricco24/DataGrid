<?php

namespace Component\DataGrid\Filters;

use Nette;

class Filter extends Nette\ComponentModel\Component 
{

	public $
	
	/**
	 * @param string $name 
	 */
	public function __construct($name) {
		parent::__construct();
		
		$this->caption = $name;
	}

	
}
