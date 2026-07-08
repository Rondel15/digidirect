define(['exports', 'eventPubSubExtended', './constants'], function (exports, _eventPubSubExtended, _constants) {
  'use strict';

  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports.Events = exports.Store = undefined;

  var _eventPubSubExtended2 = _interopRequireDefault(_eventPubSubExtended);

  var Constants = _interopRequireWildcard(_constants);

  function _interopRequireWildcard(obj) {
    if (obj && obj.__esModule) {
      return obj;
    } else {
      var newObj = {};

      if (obj != null) {
        for (var key in obj) {
          if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key];
        }
      }

      newObj.default = obj;
      return newObj;
    }
  }

  function _interopRequireDefault(obj) {
    return obj && obj.__esModule ? obj : {
      default: obj
    };
  }

  /* eslint one-var: ["error", { const: "never" }] */
  var Store = exports.Store = new _eventPubSubExtended2.default(Constants);
  var Events = exports.Events = Constants;
});
