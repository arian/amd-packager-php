
define("basic/three",
	['./one', './two'],
	function(one, two){
		return one + two;
	}
);
