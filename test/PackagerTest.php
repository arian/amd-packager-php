<?php

include '../Packager.php';

class PackagerText extends PHPUnit_Framework_TestCase {

	public function testSimple(){

		$packager = new Packager\Packager;
		$packager->setBaseUrl('fixtures');
		$packager->req(array('simple'));

		$loaded = $packager->loaded();

		$this->assertEquals(1, count($loaded));
		$this->assertTrue(isset($loaded['simple']));

	}

	public function testDependencies(){

		$packager = new Packager\Packager;
		$packager->setBaseUrl('fixtures');
		$packager->req(array('one'));

		$this->assertEquals(array(
			'one' => array(
				'two',
				'three'
			),
			'two' => array(),
			'three' => array()
		), $packager->dependencies());

	}

	public function testCustomID(){
		
		$packager = new Packager\Packager;
		$packager->setBaseUrl('fixtures');
		$packager->req(array('idtest'));
		
		$loaded = $packager->loaded();

		$this->assertTrue(isset($loaded['customid']));

	}

	public function testNoID(){

		$packager = new Packager\Packager;
		$packager->setBaseUrl('fixtures');
		$packager->req(array('noid'));

$expected = "
define('noid', function(){

});

";
		$actual = $packager->output();
		$this->assertEquals($expected, $actual);
	}

	public function testDOMNode(){

		$packager = new Packager\Packager;
		$packager->setBaseUrl('fixtures/MooTools');
		$packager->req(array('DOM/Node'));

		$loaded = $packager->dependencies();

		$this->assertEquals(array (
			'DOM/Node' => array(
				'../Core/Class',
				'../Utility/typeOf',
				'../Host/Array',
				'../Host/String',
				'../Utility/uniqueID',
			),
			'Core/Class' => array(
				'../Utility/typeOf',
				'../Utility/merge',
			),
			'Utility/typeOf' => array(),
			'Utility/merge' => array(),
			'Host/Array' => array(
				'../Core/Host',
			),
			'Core/Host' => array(),
			'Host/String' => array(
				'../Core/Host',
			),
			'Utility/uniqueID' => array (),
		), $packager->dependencies());

	}

	// TODO: Write other tests for the fixtures/Source (mootools) files

}
