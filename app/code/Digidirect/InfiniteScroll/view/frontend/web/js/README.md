## Install Babel

```
$ npm install grunt-babel babel-preset-es2015 babel-preset-stage-0 babel-plugin-add-module-exports --save-dev
```

## Usage

Config path:
```
dev/tools/grunt/configs/babel.js
```

```js
'use strict';

module.exports = {
    options: {
        sourceMap: false,
        babelrc: false,
        presets: ['es2015', 'stage-0'],
        plugins: ['add-module-exports', 'transform-es2015-modules-amd']
    },
    dist: {
        files: [{
            expand: true,
            cwd: 'vendor/digidirect/',
            src: ['**/frontend/web/js/src/**/*.js'],
            dest: 'vendor/digidirect/',
            ext: '.js',
            rename: function(dest, src) {
                return dest + src.replace('/src/', '/dist/');
            }
        }]
    }
};
```

Run task:

```
$ grunt babel
```

## ES6 Watcher

Config path:
```
dev/tools/grunt/configs/watch.js
```

```js
...
var watchOptions = {
    ...
    'es6': {
        'options': {
            livereload: true
        },
        'files': 'vendor/digidirect/**/src/**/*.js',
        'tasks': 'babel'
    }
};
...
```

Run task:

```
$ grunt watch
```

## Infinite Scroll Settings

Option | Type | Default | Description
------ | ---- | ------- | -----------
itemsContainerSelector | string | '.product-items' | Selector of the element containing your items
itemSelector | string | '> .item' | Selector of the element that each item has
rememberScrollState | boolean | false | Save scrolled results in local storage and load them on page load
scrollStateKey | string | 'infinite-scroll-state' | Name of local storage variable to save scroll state to
scrollToLastViewedItem | boolean | false | Scroll to last clicked item when going back or opening item list
itemUrlSelector | string | '.product-item-link, .product-item-photo' | Selector for attaching click events to and getting unique href attribute of the item
itemUrlKey | string | 'infinite-item-url' | Name of local storage variable to save clicked item's href attribute to
nextUrl | string | null | URL of next data
action | string  | 'click' | Set action (event) to load new data. Available 'click' and 'scroll',
buttonArea | string | '.products.wrapper' | The area to insert a button
buttonPrepend | boolean | false | Button prepend buttonArea
buttonTemplate | string |  'text!Digidirect_InfiniteScroll/template/button.html' | Affected inside 'viewCustom'
buttonContent | string |  'Load More' | Button content
scrollContainer | jQuery object | $(window) | Custom paging templates. See source for use example.
scrollOffset | int | 150 | Ability to load new data earlier than you scroll to the end of scrollContainer
requestOptions | object | {} | Supplying request options of fetch()
preFill | boolean | true | When the itemsContainerSelector is smaller than the scrollContainer, load data until the itemsContainerSelector is larger or links are exhausted
viewCustom | string |  '' | Extends core View. Example: 'Digidirect_InfiniteScroll/js/dist/views/catalog-search/index'
addOn | string | '' | Add-ons communicate between component and the global store