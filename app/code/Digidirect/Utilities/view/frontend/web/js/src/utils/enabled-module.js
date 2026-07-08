/**
 * Get Enabled Module
 * @param name
 */
export function getEnabledModule (name) {
    let modulesArray = window.enabledModules;
    if (modulesArray && modulesArray.length) {
        return modulesArray.indexOf(name) != -1;
    }
}
