
describe('AMD Packager', function(){

	/*
	 * The second argument, dependencies, is an array of the dependencies that
	 * are required by the module that is being defined. The dependencies must
	 * be resolved prior to the execution of the module factory function, and the
	 * resolved values should be passed as arguments to the factory function
	 * with argument positions corresponding to indexes in the dependencies
	 * array. The dependencies ids may be relative ids, and should be resolved
	 * relative to the module being defined.
	 */
	describe('basic', function(){
		it('should require its dependencies and the module factory should return the correct value', function(){
			expect(packager.require('basic/three')).toEqual(3);
		});
	});

	/*
	 * If the value of "require", "exports", or "module" appear in the dependency
	 * list, the argument should be resolved to the corresponding free variable
	 * as defined by the CommonJS modules specification. This argument is optional.
	 * If omitted, it should default to ["require", "exports", "module"]. However,
	 * if the factory function's arity (length property) is less than 3, then
	 * the loader may choose to only call the factory with the number of
	 * arguments corresponding to the function's arity or length.
	 */
	describe('Modules/1.1', function(){

		it('should require another module and put it on the exports object', function(){
			expect(packager.require('modules/require').test).toEqual('a');
		});

		it('should return the module object with the correct module.id', function(){
			expect(packager.require('modules/module').id).toEqual('modules/module');
		});

		it('should return call the factory function with only the arity of the fuction', function(){
			expect(packager.require('modules/argslength')).toEqual(2);
		});

	});

	/*
	 * The third argument, factory, is a function that should be executed to
	 * instantiate the module or an object. If the factory is a function it
	 * should only be executed once. If the factory argument is an object, that
	 * object should be assigned as the exported value of the module.
	 */
	describe('factory', function(){

		it('should act as the exported value if the factory is an object', function(){
			expect(packager.require('objectfactory')).toEqual({a: 1, b: 2});
		});

	});

	// different (maybe non standard) tests

	describe('option: modules', function(){

		it('should wrap an module without AMD define() with that function so it\'s usable', function(){
			expect(packager.require('modules/no-amd').foo).toEqual('yo');
		});

		it('should have the require method available', function(){
			expect(packager.require('modules/no-amd').require).not.toBeNull();
		});

		it('should have the exports object available', function(){
			expect(packager.require('modules/no-amd').exports).not.toBeNull();
		});

		it('should have the modules object available', function(){
			expect(packager.require('modules/no-amd').module).not.toBeNull();
			expect(packager.require('modules/no-amd').module.id).toEqual('modules/no-amd');
		});

	});


});
