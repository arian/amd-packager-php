<?php

include_once dirname(__FILE__) . '/Abstract.php';

class Minifier_YUI extends Minifier {

	static $JAR_PATH = '/home/arian/www/yuicompressor.jar';

	static public function compress($code){
		$temp_file = sys_get_temp_dir() .  '/amd-packager.js';
		file_put_contents($temp_file, $code);
		return shell_exec('java -jar ' . self::$JAR_PATH . ' --type js ' . $temp_file);
	}

}
