<?php

include_once dirname(__FILE__) . '/Abstract.php';

class Minifier_UglifyJS extends Minifier {

	static public function compress($code){
		$temp_file = sys_get_temp_dir() .  '/amd-packager.js';
		file_put_contents($temp_file, $code);
		return shell_exec('uglifyjs ' . $temp_file);
	}

}
