/* eslint no-unused-vars: [1] */
// import Model from './../models/index';
import Component from './../common/component';
import $ from 'jquery';
import {Store, Events} from './../common/store';
import {XMLHttpRequest} from 'xmlhttprequest';
global.XMLHttpRequest = XMLHttpRequest;
const fetchMock = require('fetch-mock');

describe('Component', () => {
    let model,
        component,
        options = {
            itemsContainerSelector: '.product-items',
            itemSelector: '> .item',
            nextUrl: null,
            action: 'click',
            buttonArea: '.products.wrapper',
            buttonPrepend: false,
            buttonContent: 'Load More',
            scrollContainer: $(window),
            scrollOffset: 150,
            requestOptions: {},
            preFill: false,
            viewCustom: '',
            addOn: ''
        };

    beforeAll(() => {
        component = new Component(options);
        // model = new Model(options);
    });

    afterEach(() => {
        fetchMock.restore();
    });

    describe('fetchData', () => {
        test('should return true if no passed URL', async () => {
            expect.assertions(1);
            const test = await component.fetchData();

            expect(test).toBe(true);
        });

        test('should trigger DATA_FETCH_SUCCESS event', async () => {
            fetchMock.get('http://example.com/', {
                content: '<div>test content</div>',
                url: 'http://myjson.com/'
            });

            const response = await component.fetchData('http://example.com/');
            expect(Store.currentState).toEqual('DATA_FETCH_SUCCESS');
        });

        test('should trigger DATA_FETCH_FINISH event', async () => {
            fetchMock.get('http://example.com/', {
                content: '<div>test content</div>',
                url: ''
            });

            const response = await component.fetchData('http://example.com/');
            expect(Store.currentState).toEqual('DATA_FETCH_FINISH');
        });

        test('should trigger ERROR event', async () => {
            fetchMock.get('http://bad.url', {
                status: 400,
                body: JSON.stringify('bad data')
            });

            const response = await component.fetchData('http://bad.url');
            expect(Store.currentState).toEqual('ERROR');
        });
    });
});
