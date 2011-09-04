#!/usr/bin/env node

var jasmine = require('./jasmine-node');
var sys = require('sys');
var exec = require('child_process').exec;

var showColors = true;
var isVerbose = false;
var extentions = "js";
var match = '.'

var exitCode = 0;

var onExit = function(){
	process.removeListener("exit", onExit);
	process.exit(exitCode);
};

process.on("exit", onExit);

// Our built source file
exec('php ./build.php > build.js', function(err){

	global.packager = require('./build.js');

	// The specs
	jasmine.executeSpecsInFolder(__dirname + '/spec', function(runner, log){
		sys.print('\n');
		if (runner.results().failedCount == 0) {
			exitCode = 0;
		} else {
			exitCode = 1;
		}
	}, isVerbose, showColors, new RegExp(match + "spec\\.(" + extentions + ")$", 'i'));

});
