<?php

namespace Packager;

include_once 'Path.php';

class Packager {

	protected $_baseurl;
	protected $_alias = array();
	protected $_modules = array();

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

		if (isset($this->_modules[$id])) return;

		$extension = Path::extname($id);
		$filename = $id;

		if (!$extension) $filename .= '.js';

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
		$start = $extension ? false : strpos($code, 'define');
		$collection = '';
		$_id = '';

		if ($start !== false){

			$end = false;
			$current = strpos($code, '(', $start);
			$length = strlen($code);
			$char = '';

			$string = false;
			$array = false;
			$collection = '';

			$dep = '';

			if ($current) while (true){
				$last = $char;
				$char = substr($code, $current++, 1);

				// line comments
				if (!$string && $char == '/' && $last == '/'){
					$collection = substr($collection, 0, -1);
					$current = strpos($code, "\n", $current);
					continue;
				}

				// other comments
				if (!$string && $char == '*' && $last == '/'){
					$collection = substr($collection, 0, -1);
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

				$collection .= $char;

				// We're finished
				if (!$string && $char == ']'
					|| $current > $length
					|| substr($collection, -8) == 'function'
				) break;
			}

		}

		if ($_id) $id = Path::resolve($baseurl, $_id);
		$module['id'] = $id;
		$module['dependencies'] = $deps;

		$this->_modules[$id] = $module;

		if (count($deps)) $this->req($deps, Path::dirname($id));

	}

	public function output(){
		$code = '';
		foreach ($this->_modules as $module){
			$code .= $module['content'] . PHP_EOL . PHP_EOL;
		}
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

}
