<?php

include_once dirname(__FILE__) . '/../../Packager.php';

class PackagerText extends PHPUnit_Framework_TestCase {

	protected $fixtures;

	public function setUp(){
		$this->fixtures = dirname(__FILE__) . '/../fixtures';
	}

	public function testSimple(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$packager->req(array('simple'));

		$loaded = $packager->loaded();

		$this->assertEquals(1, count($loaded));
		$this->assertTrue(isset($loaded['simple']));

	}

	public function testDependencies(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
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

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$packager->req(array('idtest'));

		$loaded = $packager->loaded();

		$this->assertTrue(isset($loaded['customid']));

	}

	public function testDotInID(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$packager->req(array('with.dots.in.filename'));

		$loaded = $packager->loaded();

		$this->assertEquals(1, count($loaded));
		$this->assertTrue(isset($loaded['with.dots.in.filename']));

	}

	public function testNoID(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$packager->req(array('noid'));

$expected = "
define('noid', function(){

});

";
		$actual = $packager->output();
		$this->assertEquals($expected, $actual);
	}

	public function testDOMNode(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$packager->addAlias('Core', 'MooTools');
		$packager->req(array('Core/DOM/Node'));

		$this->assertEquals(array(
			'Core/DOM/Node' => array(
				'Core/Core/Class',
				'Core/Utility/typeOf',
				'Core/Host/Array',
				'Core/Host/String',
				'Core/Utility/uniqueID',
			),
			'Core/Core/Class' => array(
				'Core/Utility/typeOf',
				'Core/Utility/merge',
			),
			'Core/Utility/typeOf' => array(),
			'Core/Utility/merge' => array(),
			'Core/Host/Array' => array(
				'Core/Core/Host',
			),
			'Core/Core/Host' => array(),
			'Core/Host/String' => array(
				'Core/Core/Host',
			),
			'Core/Utility/uniqueID' => array (),
		), $packager->dependencies());

	}

	public function testCrossPackageDependencies(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$packager->addAlias('PackageA', 'packageA')->addAlias('PackageB', 'packageB');
		$packager->req(array('PackageA/a'));

		$this->assertEquals(array(
			'PackageA/a' => array(
				'PackageA/b',
				'PackageA/c',
				'PackageB/b',
			),
			'PackageA/b' => array(),
			'PackageA/c' => array(),
			'PackageB/b' => array(),
		), $packager->dependencies());

	}

	// TODO: Write other tests for the fixtures/Source (mootools) files

	public function testCircular(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures . '/circular');
		$packager->req(array('../../../loader', 'a'));

	}

	public function testRequireInBody(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$packager->req(array('requireInBody'));

		$deps = $packager->dependencies();
		$this->assertEquals(array(
			'one',
			'two',
			'three',
			'simple'
		), $deps['requireInBody']);

	}

	public function testAnalyze(){
		$class = new ReflectionClass('Packager');
		$method = $class->getMethod('analyze');
		$method->setAccessible(true);

		$packager = new Packager();
		$info = $method->invoke($packager, file_get_contents($this->fixtures . '/analyze.js'));

		$this->assertEquals(
			array(
				8 => '../Core/Class',
				24 => '../Utility/typeOf',
				44 => '../Host/Array',
				60 => '../Host/String',
				77 => '../Utility/uniqueID',
				152 => 'foo',
				159 => 'bla',
				175 => '2 3',
				191 => 'foo bar'
			),
			$info['strings']
		);

		$this->assertEquals(
			array(
				7 => "'../Core/Class','../Utility/typeOf','../Host/Array','../Host/String','../Utility/uniqueID'",
				172 => "1,'2 3',4",
				190 => "'foo bar'"
			),
			$info['arrays']
		);

	}

}
