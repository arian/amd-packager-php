<?php

include_once dirname(__FILE__) . '/Packager.php';

class Packager_Builder {

	protected $_modules = array();

	public function __construct($modules){
		$this->_modules = $modules;
	}

	/**
	 * Gives an associated array with all loaded modules. The keys are the
	 * Module IDs while the value is an array with the module information.
	 * Those arrays contain the url, id and dependencies
	 *
	 * @return array
	 */
	public function loaded(){
		return $this->_modules;
	}

	/**
	 * Lists the dependencies for each module
	 *
	 * @return array
	 */
	public function dependencies(){
		$deps = array();
		foreach ($this->_modules as $id => $module) $deps[$id] = $module['dependencies'];
		return $deps;
	}

	/**
	 * Returns an array with the modules grouped by packages
	 *
	 * @return array
	 */
	public function packages(){
		$packages = array();
		foreach ($this->_modules as $module){
			if (!isset($packages[$module['package']])) $packages[$module['package']] = array();
			$packages[$module['package']][$module['id']] = $module;
		}
		return $packages;
	}

	/**
	 * Lists the loaded modules
	 *
	 * @return array
	 */
	public function modules(){
		return array_keys($this->_modules);
	}

	/**
	 * Generates the concatenated module content and gives every define() an ID
	 *
	 * @param string $glue optional The glue which joins the code of the different modules together
	 * @return string
	 */
	public function output($glue = "\n\n"){
		return $this->_output($this->_modules, $glue);
	}

	/**
	 * Concatenates the files by Package
	 * 
	 * @param string $glue optional The glue which joins the code of the different modules together
	 */
	public function outputByPackage($glue = "\n\n"){
		$codes = array();
		foreach ($this->packages() as $package => $modules){
			$codes[$package] = $this->_output($modules, $glue);
		}
		return $codes;
	}

	protected function _output($modules, $glue = "\n\n"){
		$code = array();
		foreach ($modules as $module){
			$module = $this->_setid($module);
			$code[] = $module['content'];
		}
		return implode($glue, $code);
	}

	protected function _setid($module){
		if ($module['amd']){
			$module['content'] = preg_replace('/define\((\[|\{|function)/', "define('" . $module['id'] . "', $1", $module['content']);
		}
		return $module;
	}

}
