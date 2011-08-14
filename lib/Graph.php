<?php

include_once dirname(__FILE__) . '/Path.php';
include_once dirname(__FILE__) . '/Builder.php';

/**
 * Outputs a dependency graph by generating a DOT (http://en.wikipedia.org/wiki/DOT_language)
 * and passing that into the `dot` executable
 *
 * @see http://www.graphviz.org/
 */

class Packager_Graph {

	protected $_builder;

	public function __construct(Packager_Builder $builder){
		$this->_builder = $builder;
	}

	protected function _generateDigraph(){

		$graph = 'digraph finite_state_machine {' . PHP_EOL;

		foreach ($this->_builder->dependencies() as $id => $deps){
			foreach ($deps as $dep) $graph .= '"' . addslashes($dep) . '" -> "' . addslashes($id) . '";' . PHP_EOL;
		}

		$graph .= '}';

		return $graph;
	}

	public function output($filename, $type = null){
		if (!$type){
			$extension = strtolower(Path::extname($filename));
			$types = array(
				'.svg' => 'svg',
				'.png' => 'png',
				'.gif' => 'gif',
				'.jpg' => 'jpg',
				'.vg' => 'canon',
				'.txt' => 'plain-ext'
			);
			if (isset($types[$extension])) $type = $types[$extension];
			else {
				$type = 'svg';
				$filename .= '.svg';
			}
		}

		$dg = $this->_generateDigraph();
		$cmd = 'echo ' . escapeshellarg($dg) . ' | dot -T' . $type;
		$result = shell_exec($cmd);
		file_put_contents($filename, $result);
		return $cmd;
	}

}
