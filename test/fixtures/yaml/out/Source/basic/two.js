/*
---
name: basic/two
description: basic/two
requires: [basic.one]
provides: basic.two
...
*/


define(["./one"], function(one){
	return one * 2;
});
