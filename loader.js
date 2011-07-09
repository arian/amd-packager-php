
var define, require;

(function(){//define + require, default basic implementation (subset) of the commonJS AMD spec

var slice = Array.prototype.slice;
var relToAbs = function(name, current){
	if (name.indexOf('./') == 0) return current.slice(0, current.lastIndexOf('/') + 1) + name.slice(2);
	if (name.indexOf('../') == 0){
		var up = name.split('../').length,
			nameParts = name.split('/'), currentParts = current.split('/');
		return slice.call(currentParts, 0, -up).concat(slice.call(nameParts, up - 1)).join('/');
	}
	return name;
};

var modules = {}, loaded = {};

if (!define) define = function(id, dependencies_, factory_){
	var dependencies = (factory_) ? dependencies_ : [];
	for (var i = dependencies.length; i--;) dependencies[i] = relToAbs(dependencies[i], id);
	modules[id] = {dependencies: dependencies, factory: factory_ || dependencies_};
};

if (!require) require = function(ids, callback_){
	if (typeof ids == 'string') ids = [ids];
	var modules_ = [];
	for (var i = 0; i < ids.length; i++){
		var id = ids[i], module = modules[id];
		if (module && loaded[id] == null){
			var factory = module.factory, ideps = module.dependencies, dependencies = [];
			for (var j = 0; j < ideps.length; j++) dependencies.push(require(ideps[j]));
			loaded[id] = (typeof factory == 'function') ? factory.apply(this, dependencies) : factory;
		}
		modules_.push(loaded[id]);
	}
	if (callback_) callback_.apply(this, modules_);
	return modules_[0];
};

})();
