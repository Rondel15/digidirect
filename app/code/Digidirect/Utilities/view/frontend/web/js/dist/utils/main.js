define(['exports', './fetch', './load-view', './load-addOn'], function (exports, _fetch, _loadView, _loadAddOn) {
  'use strict';

  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports.loadAddOn = exports.loadView = exports.callFetch = undefined;
  exports.callFetch = _fetch.callFetch;
  exports.loadView = _loadView.loadView;
  exports.loadAddOn = _loadAddOn.loadAddOn;
});
