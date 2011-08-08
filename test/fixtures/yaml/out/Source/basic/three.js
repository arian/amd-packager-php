/*
---
name: basic/three
description: basic/three
requires: [basic.one, basic.two]
provides: basic.three
...
*/


define("basic/three",
	['./one', './two'],
	function(one, two){
		return one + two;
	}
);
