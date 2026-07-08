/* eslint one-var: ["error", { const: "never" }] */
import EventPubSub from './../../vendor/event-pubsub';
import * as Constants from './constants';

export const GlobalStore = new EventPubSub();
export const GlobalEvents = Constants;
