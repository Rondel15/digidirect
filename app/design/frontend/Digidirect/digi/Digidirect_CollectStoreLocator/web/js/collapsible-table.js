define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('digidirect.collapsibleTable', {
        options: {
            container: '[data-role="collapsible-table-container"]',
            showClass: '-show',
            hideClass: '-hide'
        },

        _create: function () {
            if (this.element.length) {
                this.container = this.element;
                this.table = this.container.find('table');

                this._rowAction();
                this._attachEvents();
            }
        },

        _rowAction: function(action) {
            var $self = this,
                //rows = $self.table.find('tr:not(:first-child)'),
                rows = $self.table.find('tr'),
                $action = action || 'hide';

            switch ($action) {
                case 'hide':
                    rows.removeClass($self.options.showClass);
                    break;
                case 'show':
                    rows.addClass($self.options.showClass);
                    break;
                default:
                    rows.removeClass($self.options.showClass);
                    break;
            }
        },
        _attachEvents: function () {
            var $self = this;

            $self.container.on('click', function (e) {
                $(this).toggleClass('-active');

                if($(this).is('.-active')) {
                    $self._rowAction('show');
                } else {
                    $self._rowAction('hide');
                }

                e.stopPropagation();
            });
        }
    });

    return $.digidirect.collapsibleTable;
});
