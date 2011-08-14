<?php

include_once dirname(__FILE__) . '/../../lib/Packager.php';
include_once dirname(__FILE__) . '/../../lib/Graph.php';

class GraphTest extends PHPUnit_Framework_TestCase {

	protected $fixtures;

	public function setUp(){
		$this->fixtures = dirname(__FILE__) . '/../fixtures';
	}

	public function testTree(){
		
		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures . '/MooTools');
		$builder = $packager->req(array('DOM/Node'));

		$graph = new Packager_Graph($builder);
		$graph->output(dirname(__FILE__) . '/hello.png');

	}

}
