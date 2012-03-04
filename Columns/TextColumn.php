<?php

namespace Component\DataGrid\Columns;

use Nette,
		Nette\Diagnostics\Debugger;

class TextColumn extends Column
{
	
	public function __construct($parent, $name) {
		parent::__construct($parent, $name);

		$this->kind = 'text';
	}
	
}
