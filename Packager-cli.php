#!/usr/bin/env php
<?php

include_once 'Packager.php';

$packager = new Packager\Packager;

$options = include 'options.php';

if (isset($options['paths'])) foreach ($options['paths'] as $alias => $path){
	$packager->addAlias($alias, $path);
}

if (isset($options['baseurl'])) $packager->setBaseUrl($options['baseurl']);

$requires = $argv;
array_shift($requires);

if ($options['loader']) array_unshift($requires, 'loader.js');

$packager->req($requires);

echo $packager->output();

/*
$loaded = $packager->loaded();

foreach ($loaded as $module){
	$module['content'] = null;
	print_r($module);
}
*/