/*packager-amd modules: true */

var a = require('./a'),
	mod = require('./module');

exports.foo = 'yo';

exports.require = require;
exports.exports = exports;
exports.module = module;

