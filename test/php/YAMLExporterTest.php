<?php

include_once dirname(__FILE__) . '/../../Packager.php';
include_once dirname(__FILE__) . '/../../YAMLExporter.php';

class YAMLExporterTest extends PHPUnit_Framework_TestCase {

	protected $fixtures;

	public function setUp(){
		$this->fixtures = dirname(__FILE__) . '/../fixtures';
	}

	public function testHeader(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$builder = $packager->req(array('yaml/basic/three'));

		$export = new Packager_YAMLExporter($builder);
		$export->setPackageJSON($this->fixtures . '/yaml/basic/package.json');

		$export->save($this->fixtures . '/yaml/out');

	}

}
