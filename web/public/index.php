<?php

error_reporting(E_ALL);

include_once dirname(__FILE__) . '/../../lib/Path.php';
include_once dirname(__FILE__) . '/../../lib/Packager.php';
include_once dirname(__FILE__) . '/../lib/Template.php';

$options = array(
	'baseurl' => '/home/arian/www',
	'packages' => array(
		'Core' => 'MooTools/core2-define/Source',
		'Centaur' => 'abacus/centaur/Source',
		'dojo' => 'MooTools/dojo'
	),
	'modules' => array(
		'Core/DOM/Node',
		'Centaur/App',
		'dojo/array',
		'dojo/fx/Toggler'
	),
	'cache' => true,
	'minifier' => array(
		'yui' => '/home/arian/www/yuicompressor.jar',
		'uglify-js' => true,
	),
	'download' => array(
		'exports' => 'packaged.js',
		'contenttype' => 'application/javascript',
		'charset' => 'UTF-8'
	)
);

$cache_file = dirname(__FILE__) . '/../cache/modules.json';

if (!empty($options['cache']) && file_exists($cache_file)){
	$builder = Packager_Builder::fromJSON(file_get_contents($cache_file));
} else {

	$packager = new Packager;
	$packager->setBaseUrl($options['baseurl']);
	foreach ($options['packages'] as $alias => $url){
		$packager->addAlias($alias, $url);
	}
	$builder = $packager->req($options['modules']);
	
	if (!empty($options['cache'])){
		file_put_contents($cache_file, $builder->toJSON());
	}
}

if ($_SERVER['REQUEST_METHOD'] != 'POST'){

	$tpl = new Template(dirname(__FILE__) . '/../views');

	$tpl->assign('BASE_PATH', Path::dirname($_SERVER['SCRIPT_NAME']));
	$tpl->assign('packages', $builder->packages());
	$tpl->assign('options', $options);

	echo $tpl->fetch('packager.php');

} else {

	$hasZip = class_exists('ZipArchive');

	$builder->reduce($_POST['modules']);

	$files = array();
	if ($hasZip && $_POST['concatenation'] == 'package'){
		$files = $builder->outputByPackage();
	} else {
		$files['single'] = $builder->output();
	}

	if ($_POST['compressor'] == 'yui' && !empty($options['minifier']['yui'])){

		include_once dirname(__FILE__) . '/../lib/Minifier/YUI.php';
		if (is_string($options['minifier']['yui'])) Minifier_YUI::$JAR_PATH = $options['minifier']['yui'];
		foreach ($files as &$code) $code = Minifier_YUI::compress($code);

	} elseif ($_POST['compressor'] == 'ugly' && !empty($options['minifier']['uglify-js'])){

		include_once dirname(__FILE__) . '/../lib/Minifier/UglifyJS.php';
		foreach ($files as &$code) $code = Minifier_UglifyJS::compress($code);

	}

	if (count($files) > 1){

		$zip = new ZipArchive();
		$temp_file = tempnam(sys_get_temp_dir(), 'amd-packager.zip');

		if ($zip->open($temp_file, ZIPARCHIVE::CREATE) !== TRUE){
			throw new Exception('Could not open ' . $temp_file);
		}

		foreach ($files as $file => $_code){
			$zip->addFromString($file . '.js', $_code);
		}

		$zip->close();
		
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="packaged.zip"');

		echo file_get_contents($temp_file);

	} else {

		header('Content-Type: ' . $options['download']['contenttype'] . '; charset=' . $options['download']['charset']);
		header('Content-Disposition: attachment; filename="' . $options['download']['exports'] . '"');

		echo reset($files);

	}

}

