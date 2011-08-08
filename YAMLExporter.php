<?php

class YAMLExporter {

	protected $_module;

	public function __construct($module){
		$this->_module = $module;
	}

	public function generate(){
		return "/*\n"
			. "---\n"
			. "name: " . $this->_module['id'] . "\n"
			. "description: " . $this->_module['id'] . "\n"
			. "requires: [" . implode(', ', $this->_module['dependencies']) . "]\n"
			. "provides: " . $this->_module['id'] . "\n"
			. "...\n"
			. "*/\n";
	}

	public function save($filename = null){
		if (!$filename) $filename = $this->_module['filename'];
		file_put_contents($filename, $this->generate() . "\n" . $this->_module['content']);
	}
	
}
