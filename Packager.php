<?php

namespace Packager;

include_once 'Path.php';

class Packager {

	protected $_baseurl;
	protected $_alias = array();
	protected $_modules = array();
	protected $_files = array();

	public function __construct(){
		$this->_baseurl = __DIR__;
	}

	public function setBaseUrl($url){
		$this->_baseurl = $url;
		return $this;
	}

	public function addAlias($alias, $url){
		$this->_alias[$alias] = $url;
		return $this;
	}

	public function req(array $ids, $baseurl = null){

		foreach ($ids as &$id){

			foreach ($this->_alias as $alias => $url){
				$len = strlen($alias);
				if (substr($id, 0, $len) == $alias){
					$id = Path::resolve($url, substr($id, $len));
					break;
				}
			}

			$this->_req($id, ($baseurl && substr($id, 0, 1) == '.') ? $baseurl : $this->_baseurl);

		}

	}

	protected function _req($id, $baseurl){

		$id = Path::resolve($baseurl, $id);
		$extension = Path::extname($id);
		$filename = $id . ($extension ? '' : '.js');

		if (isset($this->_files[$filename])) return;

		$code = file_get_contents($filename);
		$module = array(
			'filename' => $filename,
			'content' => $code
		);

		/*
		define(function(){
		define('ID', function(){
		define('ID', ['first', 'second', 'third'], function(){
		define(['first', 'second', 'third'], function(){
		define([
			'first', 'second', 'thir]d'], function(){
		define([
			'first', /*comment * / 'second',
			'fourth' // more comments
		], function(){
		*/

		$deps = array();
		$_id = '';
		$start = $extension ? false : strpos($code, 'define');

		if ($start !== false){

			$current = strpos($code, '(', $start);
			$length = strlen($code);
			$char = '';

			$string = false;
			$array = false;

			$dep = '';

			if ($current) while (true){
				$last = $char;
				$char = substr($code, $current++, 1);

				// Are we finished?
				if (!$string && $char == ']'
					|| $current > $length
					|| substr($code, $current, 8) == 'function'
				) break;

				// line comments
				if (!$string && $char == '/' && $last == '/'){
					$current = strpos($code, "\n", $current) + 1;
					continue;
				}

				// other comments
				if (!$string && $char == '*' && $last == '/'){
					$current = strpos($code, '*/', $current) + 2;
					continue;
				}

				// Arrays
				if (!$string){
					if ($char == '[') $array = true;
					if ($char == ']') $array = false;
				}

				// don't want to find the end in a string, so keep track of strings
				$stringStartEnd = (($char == '"' || $char == "'") && $last != '\\');
				if ($stringStartEnd){
					$string = ($string == $char) ? false : $char;
				}

				// We're collecting the first argument: the id string
				if ($string && !$stringStartEnd && !$array) $_id .= $char;

				// Collect dependencies
				if ($array){
					if ($string && !$stringStartEnd) $dep .= $char;
					if (!$string && $stringStartEnd){
						$deps[] = $dep;
						$dep = '';
					}
				}

			}

		}

		if ($_id) $id = Path::resolve($baseurl, $_id);
		$module['id'] = $id;
		$module['dependencies'] = $deps;

		$this->_modules[$id] = $module;
		$this->_files[$filename] = $id;

		if (count($deps)) $this->req($deps, Path::dirname($id));

	}

	public function output($glue = "\n\n"){
		$code = '';
		foreach ($this->_modules as $module) $code .= $module['content'] . $glue;
		return $code;
	}

	public function loaded(){
		return $this->_modules;
	}

	public function dependencies(){
		$deps = array();
		foreach ($this->_modules as $id => $module) $deps[$id] = $module['dependencies'];
		return $deps;
	}
	
	public function modules(){
		return array_values($this->_files);
	}

}
