#!/usr/bin/env php
<?php

include_once dirname(__FILE__) . '/Packager.php';

$args = array_slice($argv, 1);
$options_file = 'options.php';
$method = 'output';
$output_file = null;
$graph_file = null;

function help(){
	echo "\npackager-cli.php [options] <modules>\n\n"
	   . "Options:\n"
	   . "  -h --help             Show this help\n"
	   . "  --options             Specify another options file (defaults to options.php)\n"
	   . "  --output              The file the output should be written to\n"
	   . "  --modules --list      List the modules\n"
	   . "  --dependencies        List the dependencies map\n"
	   . "  --graph               Create a structural dependency graph\n"
	   . "                        and write it to this file\n"
	   . "\n";
	exit;
}

function warn($message){
	$std_err = fopen('php://stderr', 'w');
	fwrite($std_err, $message);
	fclose($std_err);
}

$requires = array();

for ($i = 0, $l = count($args); $i < $l; $i++){
	$arg = $args[$i];

	switch ($arg){
		case '-h': case '--help': help(); break;
		case '--options':
			$options_file = $args[++$i];
		break;
		case '--output':
			$output_file = $args[++$i];
		break;
		case '--graph':
			$graph_file = $args[++$i];
			$method = 'graph';
		break;
		case '--modules': case '--list':
			$method = 'modules';
		break;
		case '--dependencies':
			$method = 'dependencies';
		break;
		default:
			$requires[] = $arg;
		break;
	}
}

if (empty($requires)) help();

$packager = new Packager;

$options = include $options_file;

if (isset($options['baseurl'])){
	$packager->setBaseUrl($options['baseurl']);
}

if (isset($options['paths'])) foreach ($options['paths'] as $alias => $path){
	$packager->addAlias($alias, $path);
}

if ($options['loader']) array_unshift($requires, dirname(__FILE__) . '/loader.js');

$builder = $packager->req($requires);

if ($method == 'output' || $method == 'modules'){
	warn("\nLoaded Modules:\n  " . implode("\n  ", $builder->modules()) . "\n\n");
}

if ($method == 'output'){
	$output = $builder->output();
	if ($output_file) file_put_contents ($output_file, $output);
	else echo $output;
} elseif ($method == 'dependencies'){
	$modules = $builder->dependencies();

	$str = '';
	foreach ($modules as $id => $deps){
		$str .= "\n  " . $id;
		foreach ($deps as $dep){
			$str .= "\n    - " . $dep;
		}
	}
	$str .= "\n\n";

	warn($str);
}

if ($graph_file){
	include_once dirname(__FILE__) . '/Graph.php';
	$graph = new Packager_Graph($builder);
	$graph->output($graph_file);
	warn("The dependency graph has been written to '" . $graph_file . "'\n");
}
