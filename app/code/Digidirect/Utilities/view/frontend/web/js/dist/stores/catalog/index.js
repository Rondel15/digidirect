define(['exports', './../../vendor/event-pubsub', './constants'], function (exports, _eventPubsub, _constants) {
  'use strict';

  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports.GlobalEvents = exports.GlobalStore = undefined;

  var _eventPubsub2 = _interopRequireDefault(_eventPubsub);

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
  var GlobalStore = exports.GlobalStore = new _eventPubsub2.default();
  var GlobalEvents = exports.GlobalEvents = Constants;
});
