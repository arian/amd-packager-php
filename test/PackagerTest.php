<?php

include '../Packager.php';

class PackagerText extends PHPUnit_Framework_TestCase {

	public function testSimple(){

		$packager = new Packager\Packager;
		$packager->setBaseUrl('fixtures');
		$packager->req(array('simple'));

		$loaded = $packager->loaded();

		$this->assertEquals(1, count($loaded));
		$this->assertTrue(isset($loaded['fixtures/simple']));

	}

	public function testDependencies(){

		$packager = new Packager\Packager;
		$packager->setBaseUrl('fixtures');
		$packager->req(array('one'));

		$this->assertEquals(array(
			'fixtures/one' => array(
				'two',
				'three'
			),
			'fixtures/two' => array(),
			'fixtures/three' => array()
		), $packager->dependencies());

	}

	public function testCustomID(){
		
		$packager = new Packager\Packager;
		$packager->setBaseUrl('fixtures');
		$packager->req(array('idtest'));
		
		$loaded = $packager->loaded();

		$this->assertTrue(isset($loaded['fixtures/customid']));

	}

	public function testDOMNode(){

		$packager = new Packager\Packager;
		$packager->setBaseUrl('fixtures/MooTools');
		$packager->req(array('DOM/Node'));

		$loaded = $packager->dependencies();

		$this->assertEquals(array (
			'fixtures/MooTools/DOM/Node' => array(
				'../Core/Class',
				'../Utility/typeOf',
				'../Host/Array',
				'../Host/String',
				'../Utility/uniqueID',
			),
			'fixtures/MooTools/Core/Class' => array(
				'../Utility/typeOf',
				'../Utility/merge',
			),
			'fixtures/MooTools/Utility/typeOf' => array(),
			'fixtures/MooTools/Utility/merge' => array(),
			'fixtures/MooTools/Host/Array' => array(
				'../Core/Host',
			),
			'fixtures/MooTools/Core/Host' => array(),
			'fixtures/MooTools/Host/String' => array(
				'../Core/Host',
			),
			'fixtures/MooTools/Utility/uniqueID' => array (),
		), $packager->dependencies());

	}

	// TODO: Write other tests for the fixtures/Source (mootools) files

}
