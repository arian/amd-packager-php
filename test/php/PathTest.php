<?php

include dirname(__FILE__) . '/../../Path.php';

class PathTest extends PHPUnit_Framework_TestCase {

	public function testResolve(){
		$resolved = Path::resolve('/foo/bar', '../one/two', './three');
		$this->assertEquals('/foo/one/two/three', $resolved);

		$resolved = Path::resolve('foo/../../bar', '../one/two', './three');
		$this->assertEquals('../one/two/three', $resolved);

		$resolved = Path::resolve('foo/bar', '//abc');
		$this->assertEquals('foo/bar/abc', $resolved);

		$resolved = Path::resolve('../../path/to/Source', '/filename.js');
		$this->assertEquals('../../path/to/Source/filename.js', $resolved);

		$resolved = Path::resolve('../../../path/to/Source', '/filename.js');
		$this->assertEquals('../../../path/to/Source/filename.js', $resolved);

	}

	public function testDirname(){
		$dirname = Path::dirname('/one/two/three.js');
		$this->assertEquals('/one/two', $dirname);
	}

	public function testExtaname(){
		$ext = Path::extname('/one/two/three.js');
		$this->assertEquals('.js', $ext);

		$ext = Path::extname('/one/two/three.tar.gzip');
		$this->assertEquals('.gzip', $ext);


		$ext = Path::extname('/one/two/three');
		$this->assertEquals('', $ext);
	}

}

