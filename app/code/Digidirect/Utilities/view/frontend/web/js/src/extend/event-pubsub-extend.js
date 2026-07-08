import EventPubSub from './../vendor/event-pubsub';
import {emitExtend} from './emit-extend';

export default class EventPubSubExtended extends EventPubSub {
    constructor (scope) {
        super(scope);
        this.scope = scope;
    }

    emit (type, ...args) {
        emitExtend(this, type, this.scope);
        super.emit(type, ...args);
    }
}
