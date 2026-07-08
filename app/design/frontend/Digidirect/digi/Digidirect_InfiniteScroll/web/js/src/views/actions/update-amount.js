import $ from 'jquery';

export default class Amount {
    constructor (perPageCount, currentCount, totalCount, block = $('#toolbar-amount')) {
        this.perPageCount = parseInt(perPageCount, 10);
        this.currentCount = parseInt(currentCount, 10);
        this.totalCount = parseInt(totalCount, 10);
        this.block = block;
        this._render();
    }
    _render () {
        this.block.html(this._updateAmount());
    }
    _updateAmount () {
        if (this._getLastNumber() > 1) {
            return `Showing: 1 - ${this.currentCount} of ${this.totalCount}`;
        } else if (this.totalCount === 1) {
            return `${this.totalCount} Item`;
        } else {
            return `${this.totalCount} Items`;
        }
    }
    _getLastNumber () {
        if (this.totalCount === 0) {
            return 1;
        } else if (this.perPageCount) {
            return Math.ceil(this.totalCount / this.perPageCount);
        } else {
            return 1;
        }
    }
}
