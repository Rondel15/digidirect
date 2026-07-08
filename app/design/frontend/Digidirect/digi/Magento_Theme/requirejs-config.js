var config = {
    paths: {
        'owl_carousel': 'Magento_Theme/js/owl.carousel',
        'owl_config': 'Magento_Theme/js/owl.config'
    },
    shim: {
        owl_carousel: {
            deps: ['jquery']
        },
        owl_config: {
            deps: ['jquery','owl_carousel']
        }
    }
};