<?php

include_once dirname(__FILE__) . '/../../lib/Packager.php';

class BuilderTest extends PHPUnit_Framework_TestCase {

	protected $fixtures;

	public function setUp(){
		$this->fixtures = dirname(__FILE__) . '/../fixtures';
	}

	// Builder API

	public function testLoaded(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$builder = $packager->req(array('simple'));

		$loaded = $builder->loaded();

		$this->assertEquals(1, count($loaded));
		$this->assertTrue(isset($loaded['simple']));
		$this->assertTrue($loaded['simple']['amd']);
		$this->assertEquals('simple', $loaded['simple']['id']);
		$this->assertTrue(is_array($loaded['simple']['dependencies']));

	}

	public function testDependencies(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$builder = $packager->req(array('one'));

		$this->assertEquals(array(
			'one' => array(
				'two',
				'three'
			),
			'two' => array(),
			'three' => array()
		), $builder->dependencies());

	}

	public function testPackages(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$packager->addAlias('PackageA', 'packageA')->addAlias('PackageB', 'packageB');
		$builder = $packager->req(array('PackageA/a'));

		$packages = $builder->packages();

		$this->assertTrue(isset($packages['PackageA']));
		$this->assertTrue(isset($packages['PackageB']));

		$this->assertEquals(
			array('PackageA/a', 'PackageA/b', 'PackageA/c'),
			array_keys($packages['PackageA'])
		);
		$this->assertEquals(
			array('PackageB/b'),
			array_keys($packages['PackageB'])
		);

	}

	public function testModules(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$builder = $packager->req(array('one'));

		$this->assertEquals(
			array('one', 'two', 'three'),
			$builder->modules()
		);

	}

	public function testFiles(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$builder = $packager->req(array('one'));

		$this->assertEquals(
			array(
				Path::resolve($this->fixtures, 'one.js'),
				Path::resolve($this->fixtures, 'two.js'),
				Path::resolve($this->fixtures, 'three.js')
			),
			$builder->files()
		);

	}

	public function testOutput(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$builder = $packager->req(array('simple'));

		$expected = "define(\"simple\",\n"
			. "  function() {\n"
			. "    return {\n"
			. "      color: \"blue\"\n"
			. "    };\n"
			. "  }\n"
			. ");\n";

		$this->assertEquals($expected, $builder->output());

	}

	public function testOutputNoID(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$builder = $packager->req(array('noid'));

		$expected = "\n"
			. "define('noid', function(){\n"
			. "\n"
			. "});\n"
			. "\n";

		$actual = $builder->output();
		$this->assertEquals($expected, $actual);
	}

	public function testOutputHas(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$builder = $packager->req(array('has'));

		$builder->addHas('feature', true)
				->addHas(array('feature-1' => false, 'feature-2' => true));

		$expected = "\n"
			. "define('has', function(){\n"
			. "\n"
			. "	true;\n"
			. "	true;\n"
			. "	true;\n"
			. "	true;\n"
			. "	false\n"
			. "	true\n"
			. "	has('noidea');\n"
			. "\n"
			. "});\n";

		$actual = $builder->output();
		$this->assertEquals($expected, $actual);

	}

	public function testOutputObjectAsFactory(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$builder = $packager->req(array('objectfactory'));

		$expected = "\n"
			. "define('objectfactory', {\n"
			. "	a: 1,\n"
			. "	b: 2\n"
			. "});\n";

		$this->assertEquals($expected, $builder->output());

	}

	public function testOutputByPackage(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$packager->addAlias('PackageA', 'packageA')->addAlias('PackageB', 'packageB');
		$builder = $packager->req(array('PackageA/a', 'two'));

		$expected = array(
			'PackageA' => "\n"
				. "define('PackageA/a', ['./b', 'PackageA/c', 'PackageB/b'], function(b1, b2){\n"
				. "	return 'a';\n"
				. "});\n//----\n"
				. "define('PackageA/b', function(){\n"
				. "	return 'b';\n"
				. "});\n//----\n"
				. "define('PackageA/c', function(){\n"
				. "	return 'c';\n"
				. "});\n",
			'PackageB' => "\n"
				. "define('PackageB/b', function(){\n"
				. "	return 'b';\n"
				. "});\n",
			'two' => ''
				. 'define("two", function(){' . "\n"
				. "    return 'bar';\n"
				. "  }\n"
				. ");\n"
			);

		$this->assertEquals($expected, $builder->outputByPackage('//----'));

	}

	public function testSerialization(){

		$packager = new Packager;
		$packager->setBaseUrl($this->fixtures);
		$builder = $packager->req(array('simple'));

		$json = $builder->toJSON();
		$builder2 = Packager_Builder::fromJSON($json);

		$this->assertEquals($builder->modules(), $builder2->modules());

	}

	public function testReduce(){

		$builder = new Packager_Builder(array(
			'one'   => array('id' => 'one',   'dependencies' => array()),
			'two'   => array('id' => 'two',   'dependencies' => array('one')),
			'three' => array('id' => 'three', 'dependencies' => array('one', 'two')),
			'four'  => array('id' => 'four',  'dependencies' => array('one', 'three'))
		));

		$builder->reduce(array('two'));

		$this->assertEquals(array('two', 'one'), $builder->modules());

	}

	public function testReduceExcludes(){

		$builder = new Packager_Builder(array(
			'one'   => array('id' => 'one',   'dependencies' => array()),
			'two'   => array('id' => 'two',   'dependencies' => array('one')),
			'three' => array('id' => 'three', 'dependencies' => array('one', 'two')),
			'four'  => array('id' => 'four',  'dependencies' => array('one', 'three'))
		));

		$builder->reduce(array('four'), array('three'));

		$this->assertEquals(array('four', 'one'), $builder->modules());

	}

	public function testExclude(){

		$modules = array(
			'one'   => array('id' => 'one',   'dependencies' => array()),
			'two'   => array('id' => 'two',   'dependencies' => array('one')),
			'three' => array('id' => 'three', 'dependencies' => array('one', 'two')),
			'four'  => array('id' => 'four',  'dependencies' => array('one', 'three'))
		);
		$builder = new Packager_Builder($modules);

		// won't be excluded, is required by four
		$builder->exclude(array('three'));
		$this->assertEquals(array('one', 'two', 'four', 'three'), $builder->modules());

		$builder = new Packager_Builder($modules);

		// now also exclude basic/four
		$builder->exclude(array('three', 'four'));
		$this->assertEquals(array('one', 'two'), $builder->modules());

	}

	public function testExcludeForced(){

		$modules = array(
			'one'   => array('id' => 'one',   'dependencies' => array()),
			'two'   => array('id' => 'two',   'dependencies' => array('one')),
			'three' => array('id' => 'three', 'dependencies' => array('one', 'two')),
			'four'  => array('id' => 'four',  'dependencies' => array('one', 'three'))
		);
		$builder = new Packager_Builder($modules);

		// will be excluded
		$builder->excludeForced(array('three'));
		$this->assertEquals(array('one', 'two', 'four'), $builder->modules());

		$builder = new Packager_Builder($modules);

		// now also exclude basic/four
		$builder->excludeForced(array('one', 'four'));
		$this->assertEquals(array('two', 'three'), $builder->modules());

	}

}
