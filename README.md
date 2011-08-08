AMD Packager PHP
================

A Tool to optimize your [AMD JavaScript Modules](https://github.com/amdjs/amdjs-api/wiki/AMD).
It will find the dependencies and package it into one file.

CLI
---

	packager-cli.php [options] <modules>

	Options:
	  -h --help        Show this help
	  --options        Specify another options file (defaults to options.php)
	  --output         The file the output should be written to
	  --modules / --list
					   List the modules
	  --dependencies   List the dependencies map

### Tip:

Add the following line to your `~/.bashrc` file

	alias packager-amd='~/path/to/amd-packager-php/packager-cli.php'

Now you can use the packager as:

	packager-amd Package/Module1 Package/Module2 > myTools.js


Packager Class
--------------

``` php

$packager = new Packager;

// Set baseurl

$packager->setBaseUrl(__DIR__ . '/../foo/bar');

// Add aliases

$packager->addAlias('Core', 'MooTools/Core');
$packager->addAlias('Tests', 'MooTools/Tests');

// Require

$packager->req(array('Core/DOM/Node', 'Tests/Host/Array'));

// Output

$packager->output();
$packager->output("\n---\n"); // specified glue

// Dependencies

print_r($packager->dependencies());

// List modules

print_r($packager->modules());

```

Unit Tests
----------

See the `test` folder.
Run it with `phpunit PackagerTest.php`

Requirements
------------

- PHP 5.2 (tested on 5.3, but _should_ work on 5.2)

JavaScript Examples:
--------------------

This are examples of your source files. You can already define an ID for the
module, but that's not very useful. The dependencies argument can be relative
paths to the other modules, or use the aliases.

**Source/Storage.js**: Only the factory function

``` javascript
define(function(){
	var storage = {};
	return {
		store: function(key, value){
			storage[key] = value;
			return this;
		},
		retrieve: function(key){
			return storage[key];
		}
	};
});
```

**Source/App.js**: With dependencies

``` javascript
define(['Core/Utility/typeOf', './Storage.js'], function(typeOf, Storage){
	Storage.store('foo', 'bar');
	alert(storage.retrieve('foo')); // bar
});
```

After that you can write a build script or use the CLI script.
The packager will add an ID to each `define()` function an ID so when each
`define()` is in the same file, everything continues to work. If the module
already had an ID, it will not replace it.

Notes
-----

This is not a full implementation of the AMD specification.

Some restrictions are:

- The `factory` argument MUST be a function (objects are not supported)
- It does not execute JavaScript, so the `define` function MUST be in the literal form. It also MUST use square brackets (`[` and `]`) for dependencies.


License
-------

Just the MIT-License
