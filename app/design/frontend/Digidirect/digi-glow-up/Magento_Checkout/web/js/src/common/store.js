/* eslint one-var: ["error", { const: "never" }] */
import * as Constants from './constants';
import EventPubSubExtended from 'eventPubSubExtended';

export const Store = new EventPubSubExtended(Constants);
export const Events = Constants;
