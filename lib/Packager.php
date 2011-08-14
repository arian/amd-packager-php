<?php

include_once dirname(__FILE__) . '/Path.php';
include_once dirname(__FILE__) . '/Builder.php';

class Packager {

	protected $_baseurl;
	protected $_alias = array();
	protected $_modules = array();
	protected $_files = array();
	protected $_skip = array('require', 'exports', 'module');

	public function __construct(){
		$this->_baseurl = dirname(__FILE__);
	}

	/**
	 * Sets the base path where ->req() and ->addAlias methos are relative to
	 * If the baseurl is not set, it will default to the directory of this
	 * Packager.php file
	 *
	 * @param string $url
	 * @return Packager
	 */
	public function setBaseUrl($url){
		$this->_baseurl = $url;
		return $this;
	}

	/**
	 * Adds an alias.
	 *
	 * <code>
	 * $packager->addAlias('Test', 'path/to/tests');
	 * $packager->req(array('Test/Array'));
	 * </code>
	 *
	 * @param string $alias
	 * @param string $url
	 * @return Packager
	 */
	public function addAlias($alias, $url){
		$this->_alias[$alias] = $url;
		return $this;
	}

	/**
	 * Require the desired modules.
	 * The module ids are the filenames relative to the baseurl.
	 *
	 * <code>
	 * $packager->req(array('Core/DOM/Node', 'Core/Array', 'More/Drag'));
	 * </code>
	 *
	 * @param array $ids
	 * @return Packager_Builder
	 */
	public function req(array $ids){
		foreach ($ids as $id) $this->_req($id);
		return new Packager_Builder($this->_modules);
	}

	protected function _req($id){
		if (in_array($id, $this->_skip)) return;
		if (strpos($id, '!') !== false) return;

		$filename = $id;
		$extension = Path::extname($filename);
		$amd = !in_array($extension, array('.js', '.css'/* more? */));
		if ($amd) $filename .= '.js';

		$package = '';
		foreach ($this->_alias as $alias => $url){
			$len = strlen($alias);
			if (substr($filename, 0, $len) == $alias){
				$filename = Path::resolve($url, substr($filename, $len + 1));
				$package = $alias;
				break;
			}
		}

		$filename = Path::resolve($this->_baseurl, $filename);
		if (isset($this->_files[$filename])) return;

		/*
		Syntaxis:
			define:
				define(function(){...
				define('ID', function(){...
				define('ID', ['first', 'second', 'third'], function(){..
				define(['first', 'second', 'third'], function(){...
				define({...});
			require:
				require('module');
				require(['module1', 'module2', ...]);
		*/

		$content = file_get_contents($filename);
		$deps = array();
		$_id = '';
		$amd = $amd && (strpos($content, 'define') !== false);

		if ($amd){

			$info = $this->_analyze($content);
			$code = $info['code'];
			$arrays = $info['arrays'];
			$strings = $info['strings'];

			// define(id?, dependencies?, factory)
			$defStart = strpos($code, 'define(') + 7;
			if (isset($strings[$defStart])){
				$_id = $strings[$defStart];
				$defStart += strlen($strings[$defStart]) + 3; // ",[
			}

			if (isset($arrays[$defStart])){
				$_deps = $this->_lookupArrayStrings($arrays[$defStart], $defStart, $strings);
				foreach ($_deps as $dep) $deps[] = $dep;
			}

			// require(module) / require(modules)
			$len = strlen($code);
			$i = $defStart;
			do {
				$i = strpos($code, 'require(', $i);
				if ($i === false) break;
				else $i += 8;
				if (isset($strings[$i])) $deps[] = $strings[$i];
				else if (isset($arrays[$i])){
					$_deps = $this->_lookupArrayStrings($arrays[$i], $i, $strings);
					foreach ($_deps as $dep) $deps[] = $dep;
				}
			} while ($i < $len);

		}

		if ($_id) $id = $_id;

		foreach ($deps as &$dep){
			if (substr($dep, 0, strpos($dep, '/')) != $package
				&& substr($dep, 0, 1) == '.'
			) $dep = Path::resolve($id . '/../', $dep);
		}

		$this->_modules[$id] = array(
			'id' => $id,
			'url' => $filename,
			'package' => $package,
			'amd' => $amd,
			'content' => $content,
			'dependencies' => $deps
		);

		$this->_files[$filename] = $id;

		foreach ($deps as $_dep) $this->_req($_dep);
	}

	protected function _analyze($code){
		$string = false;
		$array = false;
		$char = '';
		$rchar = '';
		$count = 0;

		$strings = array();
		$arrays = array();
		$return = '';

		for ($current = 0, $len = strlen($code); $current < $len; $current++){
			$char = substr($code, $current, 1);
			$next = substr($code, $current + 1, 1);

			// strip line comments
			if (!$string && $char == '/' && $next == '/'){
				$current = strpos($code, "\n", $current);
				continue;
			}

			// strip other comments
			if (!$string && $char == '/' && $next == '*'){
				$current = strpos($code, '*/', $current) + 1;
				continue;
			}

			// Strip whitespace
			if (!$string && ($char == ' ' || $char == "\n" || $char == "\t" || $char == "\r" || $char == "\v" || $char == "\f")){
				continue;
			}

			$last = $rchar;
			$rchar = $char;

			// Arrays
			if (!$string){
				if ($char == '[' && ($last == '(' || $last == ',' || $last == '=')) $array = $count;
				if ($char == ']') $array = false;
			}
			if ($array && $count > $array){
				if (!isset($arrays[$array])) $arrays[$array] = '';
				$arrays[$array] .= $char;
			}

			// Collect strings
			$stringStartEnd = false;
			if (($char == '"' || $char == "'") && !$string){
				$string = $char;
				$stringStart = $count;
				$stringStartEnd = true;
			}
			if (!$stringStartEnd && $char == $string && $last != '\\'){
				$string = false;
				$stringStart = false;
				$stringStartEnd = true;
			}
			if ($string && !$stringStartEnd){
				if (!isset($strings[$stringStart])) $strings[$stringStart] = '';
				$strings[$stringStart] .= $char;
			}

			$return .= $char;
			$count++;
		}

		return array(
			'strings' => $strings,
			'arrays' => $arrays,
			'code' => $return
		);
	}

	private function _lookupArrayStrings($rawArray, $start, $strings){
		$i = 0;
		$array = array();
		$len = strlen($rawArray);
		do {
			if (isset($strings[$i + $start + 1])) $array[] = $strings[$i + $start + 1];
			$i = strpos($rawArray, ',', $i);
			if ($i === false) break;
		} while (++$i < $len);
		return $array;
	}

}
