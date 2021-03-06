<?php

include dirname(__FILE__)  . '/../../lib/Packager.php';

$packager = new Packager;
$packager->setBaseUrl(dirname(__FILE__) . '/../fixtures');
$builder = $packager->req(array(
	'../../loader.js',

	'MooTools/DOM/Node',

	'basic/three',

	'modules/require',
	'modules/a',
	'modules/module',
	'modules/argslength',
	'modules/no-amd',

	'objectfactory'
));

header('Content-Type: application/javascript');

$output = $builder->output();

echo $output;
