<?php

namespace Packager;

include_once 'SB.php';

// The Path is more or less based in the Node.js implementation
// These methods are released by joyent under this license: https://github.com/joyent/node/blob/master/LICENSE

class Path {

	const SPLIT_PATH = '/^([\s\S]+\/(?!$)|\/)?((?:[\s\S]+?)?(\.[^.]*)?)$/';

	public static function normalizeArray($parts){
		$new = array();
		foreach ($parts as $i => $part){
			if ($part == '.') continue;
			elseif ($part === '..' && count($new)) array_pop($new);
			else $new[] = $part;
		}
		return $new;
	}

	public static function resolve(){
		$args = func_get_args();
		return ((substr($args[0], 0, 1) == '/') ? '/' : '')
			. ArraySB::sb($args)
				->implode('/')
				->explode('/')
				->filter(function($p){ return (bool) $p; })
				->values()
				->call(__NAMESPACE__ . '\Path::normalizeArray')
				->implode('/')
				->value();
	}

	public static function dirname($path){
		preg_match_all(self::SPLIT_PATH, $path, $out);
		if (empty($out[1][0])) return '.';
		else $dir = $out[1][0];
		return (strlen($dir) == 1) ? $dir : substr($dir, 0, -1);
	}

	public static function extname($path){
		preg_match_all(self::SPLIT_PATH, $path, $out);
		return !empty($out[3][0]) ? $out[3][0] : '';
	}

}
