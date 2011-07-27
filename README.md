AMD Packager PHP
================

A Tool to optimize your AMD JavaScript Modules.

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


Packager Class
--------------

```PHP

$packager = new Packager\Packager;

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

License
-------

Just the MIT-License

