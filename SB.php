<?php

namespace Packager;

class SB {

	public function __construct($val){
		$this->val = $val;
	}

	static public function sb($val){
		return new static($val);
	}

	public function call($fn){
		$this->val = call_user_func($fn, $this->val);
		return $this;
	}

	public function value(){
		return $this->val;
	}

}

class ArraySB extends SB {

	public function implode($separator){
		return StringSB::sb(implode('/', $this->val));
	}

	public function filter($callback){
		$this->val = array_filter($this->val, $callback);
		return $this;
	}

	public function values(){
		$this->val = array_values($this->val);
		if (is_string($this->val)) return StringSB::sb($this->val);
		elseif (is_array($this->val)) return ArraySB::sb($this->val);
		return $this;
	}

}

class StringSB extends SB {

	public function explode($separator){
		return ArraySB::sb(explode($separator, $this->val));
	}

}
