<?php

include dirname(__FILE__)  . '/../../Packager.php';

$packager = new Packager;
$packager->setBaseUrl(dirname(__FILE__) . '/../fixtures');
$packager->req(array(
	'../../loader.js',

	'MooTools/DOM/Node',

	'basic/three',

	'modules/require',
	'modules/a',
	'modules/module',
	'modules/argslength'
));

header('Content-Type: application/javascript');

$output = $packager->output();

echo $output;
