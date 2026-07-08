/**
 * Load addOn
 * @param params
 * @param context
 * @param filePath
 * @param moduleName
 */
export function loadAddOn (params, context, moduleName = '') {
    try {
        let {options} = params;
        if (typeof options === 'undefined') {
            options = params;
        }
        if (options.addOn) {
            require([options.addOn], (AddOn) => {
                context.addOn = new AddOn(params);
            });
        }
    } catch (e) {
        console.warn(moduleName + ' AddOn load failed', e);
    }
}
