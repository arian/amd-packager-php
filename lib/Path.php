<?php

// The Path is more or less based in the Node.js implementation
// These methods are released by joyent under this license: https://github.com/joyent/node/blob/master/LICENSE

class Path {

	const SPLIT_PATH = '/^([\s\S]+\/(?!$)|\/)?((?:[\s\S]+?)?(\.[^.]*)?)$/';

	/**
	 * Normalize an array path, taking care of '..' and '.' parts.
	 *
	 * @param array $parts
	 * @return array
	 */
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

	/**
	 * Resolves multiple paths and takes are of '..' and '.' parts.
	 *
	 * <code>
	 * Path::resolve('foo/bar', '../baz.html'); // foo/baz.html
	 * </code>
	 *
	 * @param string $path,... unlimited paths to resolve
	 * @return string
	 */
	public static function resolve(){
		$args = func_get_args();
		for ($l = count($args); --$l;) if (substr($args[$l], 0, 1) == '/'){
			$args = array_slice($args, $l);
			break;
		}
		$_path = explode('/', implode('/', $args));
		$path = array();
		foreach ($_path as $part) if ($part) $path[] = $part;
		$path = implode('/', self::normalizeArray($path));
		return (substr($args[0], 0, 1) == '/' ? '/' : '') . $path;
	}

	/**
	 * Returns the directory name of a path
	 *
	 * @param string $path
	 * @return string
	 */
	public static function dirname($path){
		preg_match_all(self::SPLIT_PATH, $path, $out);
		if (empty($out[1][0])) return '.';
		else $dir = $out[1][0];
		return (strlen($dir) == 1) ? $dir : substr($dir, 0, -1);
	}

	/**
	 * Returns the filename of a path
	 *
	 * @param string $path
	 * @return string
	 */
	public static function filename($path){
		preg_match_all(self::SPLIT_PATH, $path, $out);
		return (!empty($out[2][0]) && substr($out[2][0], -1) != '/') ? $out[2][0] : '';
	}

	/**
	 * Returns the file extension of a path
	 *
	 * <code>
	 * Path::extname('foo/file.html'); // returns .html
	 * </code>
	 * 
	 * @param string $path
	 * @return string
	 */
	public static function extname($path){
		preg_match_all(self::SPLIT_PATH, $path, $out);
		return !empty($out[3][0]) ? $out[3][0] : '';
	}

}
