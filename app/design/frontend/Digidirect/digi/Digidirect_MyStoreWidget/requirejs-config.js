var config = {
    map: {
        '*': {
            modalMystorewidget: 'Digidirect_MyStoreWidget/js/modal-mystorewidget'
        }
    },
    config: {
        mixins: {
            'Digidirect_MyStoreWidget/js/my-store-switcher': {
                'Digidirect_MyStoreWidget/js/my-store-switcher-mixin': true
            },
            'Digidirect_MyStoreWidget/js/view/mystore-widget': {
                'Digidirect_MyStoreWidget/js/view/mystore-widget-mixin': true
            }
        }
    }
};
