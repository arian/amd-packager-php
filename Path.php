<?php

// The Path is more or less based in the Node.js implementation
// These methods are released by joyent under this license: https://github.com/joyent/node/blob/master/LICENSE

class Path {

	const SPLIT_PATH = '/^([\s\S]+\/(?!$)|\/)?((?:[\s\S]+?)?(\.[^.]*)?)$/';

	public static function normalizeArray($parts){
		$new = array();
		foreach ($parts as $i => $part){
			if ($part == '.') continue;
			elseif ($part == '..' && count($new)){
				$first = array_pop($new);
				if ($first == '..') array_unshift($new, '..', '..');
			} else $new[] = $part;
		}
		return $new;
	}

	public static function resolve(){
		$args = func_get_args();
		$_path = explode('/', implode('/', $args));
		$path = array();
		foreach ($_path as $part) if ($part) $path[] = $part;
		$path = implode('/', self::normalizeArray($path));
		return (substr($args[0], 0, 1) == '/' ? '/' : '') . $path;
	}

	public static function dirname($path){
		preg_match_all(self::SPLIT_PATH, $path, $out);
		if (empty($out[1][0])) return '.';
		else $dir = $out[1][0];
		return (strlen($dir) == 1) ? $dir : substr($dir, 0, -1);
	}

	public static function filename($path){
		preg_match_all(self::SPLIT_PATH, $path, $out);
		return (!empty($out[2][0]) && substr($out[2][0], -1) != '/') ? $out[2][0] : '';
	}

	public static function extname($path){
		preg_match_all(self::SPLIT_PATH, $path, $out);
		return !empty($out[3][0]) ? $out[3][0] : '';
	}

}
