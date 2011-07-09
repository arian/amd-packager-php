<?php

include '../Path.php';

class PathTest extends PHPUnit_Framework_TestCase {

	public function testResolve(){
		$resolved = Packager\Path::resolve('/foo/bar', '../one/two', './three');
		$this->assertEquals('/foo/one/two/three', $resolved);

		$resolved = Packager\Path::resolve('foo/../../bar', '../one/two', './three');
		$this->assertEquals('../one/two/three', $resolved);

		$resolved = Packager\Path::resolve('foo/bar', '//abc');
		$this->assertEquals('foo/bar/abc', $resolved);
	}

	public function testDirname(){
		$dirname = Packager\Path::dirname('/one/two/three.js');
		$this->assertEquals('/one/two', $dirname);
	}

	public function testExtaname(){
		$ext = Packager\Path::extname('/one/two/three.js');
		$this->assertEquals('.js', $ext);

		$ext = Packager\Path::extname('/one/two/three.tar.gzip');
		$this->assertEquals('.gzip', $ext);


		$ext = Packager\Path::extname('/one/two/three');
		$this->assertEquals('', $ext);
	}

}

