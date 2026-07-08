/**
 * Load component view/customView
 * @param params
 * @param context
 * @param filePath
 * @param moduleName
 */
export function loadView (params, context, filePath, moduleName = '') {
    try {
        let {options} = params;
        if (typeof options === 'undefined') {
            options = params;
        }
        if (options.viewCustom) {
            filePath = './' + options.viewCustom;
        }
        // dynamic load view
        require([filePath], (View) => {
            context.view = new View(params);
        });
    } catch (e) {
        console.warn(moduleName + ' View load failed', e);
    }
}
