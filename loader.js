
var define, require;

(function(){//define + require, default basic implementation (subset) of the commonJS AMD spec

var slice = Array.prototype.slice;
var resolve = function(name, current){
	if (name.indexOf('./') == 0) return current.slice(0, current.lastIndexOf('/') + 1) + name.slice(2);
	if (name.indexOf('../') == 0){
		var up = name.split('../').length,
			nameParts = name.split('/'), currentParts = current.split('/');
		return slice.call(currentParts, 0, -up).concat(slice.call(nameParts, up - 1)).join('/');
	}
	return name;
};

var modules = {}, loaded = {};

if (!define){
	define = function(id, dependencies_, factory_){
		var factory = factory_ || dependencies_;
		var deps = (factory_) ? dependencies_ : ['require', 'exports', 'module'].slice(0, factory.length);
		for (var i = deps.length; i--;) deps[i] = resolve(deps[i], id);
		modules[id] = {
			id: id,
			deps: deps,
			factory: factory,
			exports: {},
			require: function(ids, callback){
				return require(ids, callback, id);
			}
		};
	};
	define.amd = {};
}

if (!require) require = function(ids, callback_, _relativeTo){
	if (typeof ids == 'string') ids = [ids];
	if (_relativeTo) for (var k = ids.length; k--;) ids[k] = resolve(ids[k], _relativeTo);
	var modules_ = [];
	for (var i = 0; i < ids.length; i++){
		var id = ids[i], module = modules[id];
		if (module && loaded[id] == null){
			var factory = module.factory, ideps = module.deps, deps = [], exports;
			for (var j = 0; j < ideps.length; j++){
				var idep = ideps[j], dep;
				if (idep == 'require') dep = module.require;
				else if (idep == 'exports') dep = exports = module.exports;
				else if (idep == 'module') dep = module;
				else dep = require(ideps[j]);
				deps.push(dep);
			}
			loaded[id] = (typeof factory == 'function') ? factory.apply(this, deps) : factory;
			if (loaded[id] == null) loaded[id] = exports;
		}
		modules_.push(loaded[id]);
	}
	if (callback_) callback_.apply(this, modules_);
	return modules_[0];
};

})();
