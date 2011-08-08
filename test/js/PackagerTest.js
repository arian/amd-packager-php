
describe('AMD Packager', function(){

	describe('Modules/1.1', function(){

		it('should require another module and put it on the exports object', function(){
			expect(require('modules/require').test).toEqual('a');
		});

		it('should return the module object with the correct module.id', function(){
			expect(require('modules/module').id).toEqual('modules/module');
		});

		it('should return call the factory function with only the arity of the fuction', function(){
			expect(require('modules/argslength')).toEqual(2);
		});

	});

	describe('basic', function(){
		it('should require its dependencies and the module factory should return the correct value', function(){
			expect(require('basic/three')).toEqual(3);
		});
	});

});
