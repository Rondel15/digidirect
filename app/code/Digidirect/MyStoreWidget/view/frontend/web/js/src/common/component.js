import {loadView} from 'digidirectUtils';

export default class Component {
    constructor (options) {
        this.options = options;
        this.bind(this.options);
    }

    bind (options) {
        loadView(options, this, 'Digidirect_MyStoreWidget/js/dist/view/index', 'MyStoreWidget');
    }
}
