/*
---
name: DOM
description: DOM
...
*/

define([
	'../Core/Class', '../Utility/typeOf', '../Host/Array',
	'../Host/String',/* '../Slick/Finder',*/
	'../Utility/uniqueID' //,['bar']
], function(Class, typeOf, Array, String, uniqueID){

	String['foo'] = 'bla';

	var arr = [1, '2 3', 4];
	Array(['foo bar']);

	var obj = {a: 1, b: 2};
	Array({
		c: 3,
		d: 'four'
	});

	var str = '[not an array] and {not an object}';

	return arguments;
});
