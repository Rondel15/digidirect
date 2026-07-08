import View from './../views/index';
import $ from 'jquery';

describe('View', () => {
    let view,
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
        view = new View(options);
    });

    describe('_renderData', () => {
        test('should append HTML from response json', () => {
            const responseData = {
                content: `<button class="button infinitescroll-button">Load More</button>`
            };
            document.body.innerHTML = `<div class="product-items"></div>`;

            view._renderData(responseData);

            expect($(view.options.itemsContainerSelector).html()).toEqual(responseData.content);
        });
    });
});
