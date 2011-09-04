
var define, require, packager = {};

(function(){

var modules = {}, loaded = {};

var def = packager.define = function(id, dependencies_, factory_){
	var factory = factory_ || dependencies_;
	var deps = (factory_) ? dependencies_ : ['require', 'exports', 'module'].slice(0, factory.length);
	for (var i = deps.length; i--;) deps[i] = resolve(deps[i], id);
	modules[id] = {
		id: id,
		deps: deps,
		factory: factory,
		exports: {},
		require: function(ids, callback){
			return req(ids, callback, id);
		}
	};
};
def.amd = {};

var req = packager.require = function(ids, callback_, _relativeTo){
	if (typeof ids == 'string') ids = [ids];
	if (_relativeTo) for (var k = ids.length; k--;) ids[k] = resolve(ids[k], _relativeTo);
	var modules_ = [];
	for (var i = 0; i < ids.length; i++){
		var id = ids[i], module = modules[id];
		if (module && loaded[id] == null){
			var ideps = module.deps, deps = [], exports, result = module.factory;
			for (var j = 0; j < ideps.length; j++){
				var idep = ideps[j], dep;
				switch (idep){
					case 'require': dep = module.require; break;
					case 'exports': dep = exports = module.exports; break;
					case 'module': dep = module; break;
					default: dep = req(ideps[j]);
				}
				deps.push(dep);
			}
			if (typeof result == 'function') result = result.apply(this, deps);
			loaded[id] = result != null ? result : exports;
		}
		modules_.push(loaded[id]);
	}
	if (callback_) callback_.apply(this, modules_);
	return modules_[0];
};

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

if (!require) require = req;
if (!define) define = def;

if (typeof exports != 'undefined'){
	for (var k in packager) exports[k] = packager[k];
	exports.packager = packager;
	global.define = define;
}

})();
