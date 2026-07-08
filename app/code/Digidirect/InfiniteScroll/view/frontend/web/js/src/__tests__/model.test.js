import Model from './../models/index';

describe('Model', () => {
    let model;

    beforeAll(() => {
        model = new Model();
    });

    test('should be a Class', () => {
        expect(model).toBeInstanceOf(Model);
    });

    describe('fetchData', () => {
        test('should return false if no passed URL', () => {
            expect(model.fetchData()).toBe(false);
        });
    });
});
