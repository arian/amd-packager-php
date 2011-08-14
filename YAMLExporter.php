<?php

include_once dirname(__FILE__) . '/Path.php';
include_once dirname(__FILE__) . '/Builder.php';

class Packager_YAMLExporter {

	protected $_modules;
	protected $_files = array();
	protected $_packagedata = array();

	public $srcfolder = 'Source';

	public function __construct(Packager_Builder $builder){
		$this->_modules = $builder->loaded();
	}

	public function setPackageJSON($filename){
		$json = file_get_contents($filename);
		$this->_packagedata = json_decode($json, true);
		return $this;
	}

	public function setPackageData($key, $value){
		$this->_packagedata[$key] = $value;
		return $this;
	}

	public function modules(){
		foreach ($this->_modules as $module){

			$modules = $module['dependencies'];
			$modules[] = $module['id'];

			foreach ($modules as &$moduleid){
				$_module = $this->_modules[$moduleid];
				$parts = explode('/', $moduleid);
				if (!empty($_module['package'])) array_unshift($parts);
				$moduleid = implode('.', $parts);
				if (!empty($_module['package'])) $moduleid = $module['package'] . '/' . $moduleid;
			}

			$provides = array_pop($modules);

			$header = "/*\n"
				. "---\n"
				. "name: " . $module['id'] . "\n"
				. "description: " . $module['id'] . "\n"
				. "requires: [" . implode(', ', $modules) . "]\n"
				. "provides: " . $provides . "\n"
				. "...\n"
				. "*/\n";

			$filename = Path::resolve($this->srcfolder, $module['id'] . '.js');
			$this->_files[$filename] = $header . "\n" . $module['content'];
		}
		return $this;
	}

	public function package(){
		$data = $this->_packagedata;

		$yaml = ""
			. (isset($data['name']) ? 'name: ' . $data['name'] . "\n" : '')
			. (isset($data['author']) ? 'author: ' . $data['author'] . "\n" : '')
			. (isset($data['version']) ? 'current: ' . $data['version'] . "\n" : '')
			. (isset($data['category']) ? 'category: ' . $data['category'] . "\n" : '')
			. (isset($data['keywords']) && is_array($data['keywords']) ? 'tags: [' . implode(', ', $data['keywords']) . "]\n" : '')
			. (!empty($data['licenses']) && !empty($data['licenses'][0]['type']) ? 'license: ' . $data['licenses'][0]['type'] . "\n" : '')
			. (isset($data['description']) ? 'description: "' . $data['description'] . "\"\n" : '')
			. "\n"
			. "sources:\n";

		foreach ($this->_files as $file => $content){
			$yaml .= ' - "' . $file . "\"\n";
		}

		$this->_files['package.yml'] = $yaml;

		return $this;
	}

	public function output(){
		$this->modules()->package();
		return $this->_files;
	}

	protected function forceSaveFile($path, $content){
		$dirname = Path::dirname($path);
		$dirs = array();
		$parts = explode('/', $dirname);
		for ($l = count($parts); $l--;){
			$dir = implode('/', array_slice($parts, 0, $l + 1));
			if (!is_dir($dir)) array_unshift($dirs, $dir);
			else break;
		}
		foreach ($dirs as $dir) mkdir($dir);
		return file_put_contents($path, $content);
	}

	public function save($directory){
		$files = $this->output();
		foreach ($files as $file => $content) $this->forceSaveFile(Path::resolve($directory, $file), $content);
		return $this;
	}

}
