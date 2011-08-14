<?php

include_once dirname(__FILE__) . '/../../lib/Path.php';

class Template {

	protected $values = array();
	protected $path;

	public function __construct($path){
		$this->path = $path;
	}

	public function assign($key, $value){
		$this->values[$key] = $value;
	}

	public function fetch($file){
		$view = new TemplateViewport(Path::resolve($this->path, $file), $this->values);
		return $view->fetch();
	}

}

class TemplateViewport {

	public $values;
	public $file;

	public function __construct($file, array $values){
		$this->file = $file;
		$this->values = $values;
	}

	public function partial($file){
		$file = Path::resolve(Path::dirname($this->file), '../partials', $file);
		$view = new TemplateViewport($file, $this->values);
		return $view->fetch();
	}

	public function fetch(){
		ob_start();
		extract($this->values);
		include $this->file;
		return ob_get_clean();
	}

}
