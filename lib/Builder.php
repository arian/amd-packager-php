<?php

include_once dirname(__FILE__) . '/Packager.php';

class Packager_Builder {

	protected $_modules = array();
	protected $_has = array();

	public function __construct($modules){
		$this->_modules = $modules;
	}

	/**
	 * A has() feature
	 * @param type $feature
	 * @param type $result
	 * @return Packager_Builder
	 */
	public function addHas($feature, $result = null){
		if ($result === null){
			foreach ($feature as $_feature => $_result) $this->_has[$_feature] = $_result;
		} else {
			$this->_has[$feature] = $result;
		}
		return $this;
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
	 * Returns a list of all filenames of the modules
	 *
	 * @return array
	 */
	public function files(){
		$files = array();
		foreach ($this->_modules as $module) $files[$module['url']] = $module['url'];
		return array_values($files);
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
			if (empty($package)) {
				// don't concatenate these modules that do not belong to a package
				foreach ($modules as $module){
					$codes[$module['id']] = $this->_output(array($module), $glue);
				}
			} else {
				$codes[$package] = $this->_output($modules, $glue);
			}
		}
		return $codes;
	}

	protected function _output($modules, $glue = "\n\n"){
		$code = array();
		foreach ($modules as $module){
			if (empty($module['content']) && !empty($module['url'])){
				$module['content'] = file_get_contents($module['url']);
			}
			$module = $this->_fixID($module);
			$module = $this->_replaceHas($module);
			$code[] = $module['content'];
		}
		return implode($glue, $code);
	}

	protected function _fixID($module){
		if ($module['amd']){
			$module['content'] = preg_replace('/define\((\[|\{|function)/', "define('" . $module['id'] . "', $1", $module['content']);
		}
		return $module;
	}

	protected function _replaceHas($module){
		$module['content'] = preg_replace_callback('/has ?\( ?[\'"]([\w-]+)[\'"] ?\)/i', array($this, '_replaceHasString'), $module['content']);
		return $module;
	}

	protected function _replaceHasString($match){
		$feature = $match[1];
		if (isset($this->_has[$feature])) return $this->_has[$feature] ? 'true' : 'false';
		return $match[0];
	}

	// JSON encoding and decoding

	/**
	 * Encodes the modules as JSON so it can be used elsewhere or can be cached
	 *
	 * @return string JSON
	 */
	public function toJSON(){
		return json_encode($this->_modules);
	}

	/**
	 * Decodes a JSON object and returns a new Packager_Builder object
	 *
	 * @param string $json JSON
	 * @return Packager_Builder
	 */
	static public function fromJSON($json){
		return new self(json_decode($json, true));
	}

	/**
	 * Reduces the number of modules to the given ids and their dependencies
	 *
	 * @param array $ids The new required IDs
	 * @param array $excludes Exclude certain modules (and their dependencies)
	 * @return Packager_Builder
	 */
	public function reduce(array $ids, $excludes = array()){
		$old = $this->_modules;
		$this->_modules = array();
		$this->_reduce($ids, $old, $excludes);
		return $this;
	}

	protected function _reduce($ids, $old, $excludes){
		foreach ($ids as $id){
			if (isset($old[$id])
				&& !isset($this->_modules[$id])
				&& !in_array($id, $excludes)
			){
				$this->_modules[$id] = $old[$id];
				$this->_reduce($this->_modules[$id]['dependencies'], $old, $excludes);
			}
		}
	}

	/**
	 * Exclude certain modules.
	 * However when another module depends on the excluded module, and isn't
	 * itself excluded, the module won't be excluded.
	 *
	 * @example
	 * <pre>
	 *    Class
	 *      \        typeOf
	 *     merge
	 * </pre>
	 *
	 * $builder->exclude(array('merge', 'typeof')) would exclude 'typeOf',
	 * because other modules do not have a dependency on it. However 'merge'
	 * would not be removed because it's still required by Class
	 *
	 * @param array $ids
	 * @return type
	 */
	public function exclude(array $ids){
		$reduced = array();
		foreach ($this->_modules as $id => $module){
			if (!in_array($id, $ids)) $reduced[] = $id;
		}
		return $this->reduce($reduced);
	}

	/**
	 * Excluded certain modules. Even when other modules depend on this file.
	 *
	 * @example
	 * <pre>
	 *    Class
	 *      \
	 *     merge
	 * </pre>
	 *
	 * $builder->excludeForced(array('merge')) would remove merge, even though
	 * it's required by Class
	 *
	 * @param array $ids
	 */
	public function excludeForced(array $ids){
		return $this->reduce(array_keys($this->_modules), $ids);
	}

}
