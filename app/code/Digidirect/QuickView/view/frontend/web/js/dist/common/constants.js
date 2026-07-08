define(['exports'], function (exports) {
  'use strict';

  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  /* eslint one-var: ["error", { const: "never" }] */
  //  DATA FETCHING
  var DATA_FETCH_START = exports.DATA_FETCH_START = 'DATA_FETCH_START';
  var DATA_FETCH_PROGRESS = exports.DATA_FETCH_PROGRESS = 'DATA_FETCH_PROGRESS';
  var DATA_FETCH_SUCCESS = exports.DATA_FETCH_SUCCESS = 'DATA_FETCH_SUCCESS';
  var DATA_FETCH_FINISH = exports.DATA_FETCH_FINISH = 'DATA_FETCH_FINISH';

  // QUICK VIEW EVENTS
  var QUICK_VIEW_OPEN = exports.QUICK_VIEW_OPEN = 'QUICK_VIEW_OPEN';
  var QUICK_VIEW_OPENED = exports.QUICK_VIEW_OPENED = 'QUICK_VIEW_OPENED';
  var QUICK_VIEW_CLOSE = exports.QUICK_VIEW_CLOSE = 'QUICK_VIEW_CLOSE';
  var QUICK_VIEW_CLOSED = exports.QUICK_VIEW_CLOSED = 'QUICK_VIEW_CLOSED';

  // PRODUCT PAGE EVENTS
  var SECTION_UPDATE = exports.SECTION_UPDATE = 'SECTION_UPDATE';

  var ADD_TO_CART = exports.ADD_TO_CART = 'ADD_TO_CART';

  // ERROR
  var ERROR = exports.ERROR = 'ERROR';
});
