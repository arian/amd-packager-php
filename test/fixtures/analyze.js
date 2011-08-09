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

	return arguments;
});
