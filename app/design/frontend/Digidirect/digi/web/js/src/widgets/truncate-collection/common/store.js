import EventPubSubExtended from 'eventPubSubExtended';
import * as Constants from './constants';

export const Store = new EventPubSubExtended(Constants);
export const Events = Constants;