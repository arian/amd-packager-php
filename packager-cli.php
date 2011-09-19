#!/usr/bin/env php
<?php

include_once dirname(__FILE__) . '/lib/Packager.php';

$args = array_slice($argv, 1);
$options_file = null;
$method = 'output';
$output_file = null;
$graph_file = null;
$watch = false;

function help(){
	echo "\npackager-cli.php [options] <modules>\n\n"
	   . "Options:\n"
	   . "  -h --help             Show this help\n"
	   . "  --options             Specify another options file (defaults to options.php)\n"
	   . "  --has [features]      Features for the has() api. Example: `--has feature=0 ie=1`\n"
	   . "  --require [requires]  Require these modules\n"
	   . "  -o --output           The file the output should be written to\n"
	   . "  --modules --list      List the modules\n"
	   . "  --dependencies        List the dependencies map\n"
	   . "  --graph               Create a structural dependency graph\n"
	   . "                        and write it to this file\n"
	   . "  --watch               Watches the required modules \n"
	   . "\n";
	exit;
}

function warn($message){
	$std_err = fopen('php://stderr', 'w');
	fwrite($std_err, $message);
	fclose($std_err);
}

$has = array();
$_has = false;
$requires = array();

for ($i = 0, $l = count($args); $i < $l; $i++){
	$arg = $args[$i];

	if (substr($arg, 0, 1) == '-') $_has = false;

	switch ($arg){
		case '-h': case '--help': help(); break;
		case '--options':
			$options_file = $args[++$i];
		break;
		case '--output': case '-o':
			$output_file = $args[++$i];
		break;
		case '--has':
			$_has = true;
		break;
		case '--require':
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
		case '--watch':
			$watch = true;
		break;
		default:
			if (!$_has) $requires[] = $arg;
			else {
				$feature = explode('=', $arg);
				$has[$feature[0]] = isset($feature[1])
					? ($feature[1] == 'false' || $feature[1] == '0'
						? false : true)
					: true;
			}
		break;
	}
}

if (empty($requires)) help();

$options = $options_file ? (include $options_file) : array();
if (!empty($options['has'])) $has = array_merge($options['has'], $has);

function packager(){
	global $requires, $options;

	$packager = new Packager;

	$packager->setBaseUrl(
		isset($options['baseurl']) ? $options['baseurl'] : getcwd()
	);

	if (isset($options['paths'])) foreach ($options['paths'] as $alias => $path){
		$packager->addAlias($alias, $path);
	}

	if (!empty($options['loader'])) array_unshift($requires, dirname(__FILE__) . '/loader.js');

	return $packager;
}

function build($builder){
	global $method, $has, $output_file, $graph_file;

	if ($method == 'output' || $method == 'modules'){
		warn("\nLoaded Modules:\n  " . implode("\n  ", $builder->modules()) . "\n\n");
	}

	$builder->addHas($has);

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
		include_once dirname(__FILE__) . '/lib/Graph.php';
		$graph = new Packager_Graph($builder);
		$graph->output($graph_file);
		warn("The dependency graph has been written to '" . $graph_file . "'\n");
	}

}

$builder = packager()->req($requires);

if ($watch){
	$files = $builder->files();
	$times = array();
	while (true){
		foreach ($files as $file){
			$time = filemtime($file);
			if (empty($times[$file])){
				$times[$file] = $time;
			} elseif ($time != $times[$file]){
				$times[$file] = $time;
				warn("\nThe file '" . $file . "' has changed\n");
				$builder = packager()->req($requires);
				$files = $builder->files();
				build($builder);
				break;
			}
		}
		sleep(1);
	}
} else {
	build($builder);
}
