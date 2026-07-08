/* global XMLHttpRequest, DOMParser */
(function () {
    'use strict';
    var xhr = new XMLHttpRequest(),
        config = window.svgSpriteConfig,
        path = config.path,
        revision = config.revision,
        className = config.className;

    // Check whether the file has changed
    if (canWriteToLS() && isFileCached(revision)) {
        insertSvg(window.localStorage.getItem('inlineSVGdata'));
    } else {
        sendRequest();
    }

    /**
     * Send Request for SVG sprite
     */
    function sendRequest () {
        xhr.open('GET', path, true);
        xhr.send();
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) {
                return;
            }
            if (xhr.status === 200) {
                var data = xhr.responseText;
                if (canWriteToLS()) {
                    window.localStorage.setItem('inlineSVGdata', data);
                    window.localStorage.setItem('inlineSVGRevision', revision);
                }
                insertSvg(data);
            } else {
                clearSvgLocalStorage(xhr.status, xhr.statusText);
            }
        };
    }

    /**
     * Quick way to determine whether a SVG file has been cached locally
     * @param revision
     * @returns {string|*|boolean}
     */
    function isFileCached (revision) {
        return window.localStorage.getItem('inlineSVGdata') && (window.localStorage.getItem('inlineSVGRevision') === revision);
    }

    /**
     * Insert SVG sprite to page
     */
    function insertSvg (svgData) {
        var svg,
            parser = new DOMParser();
        svg = parser.parseFromString(svgData, 'text/html').body.firstChild;
        svg.setAttribute('class', className);
        svg.style.display = 'none';
        document.body.insertBefore(svg, document.body.firstChild);
    }

    /**
     * Clear localStorage if file not found
     * @param status
     * @param statusText
     */
    function clearSvgLocalStorage (status, statusText) {
        if (canWriteToLS()) {
            window.localStorage.removeItem('inlineSVGdata');
            window.localStorage.removeItem('inlineSVGRevision');
            console.warn(status + ' ' + statusText);
        }
    }

    /**
     * Check support of localStorage (Safari private mode)
     * @returns {boolean}
     */
    function canWriteToLS () {
        try {
            window.localStorage.setItem('_canWriteToLS', 1);
            window.localStorage.removeItem('_canWriteToLS');
            return true;
        } catch (e) {
            return false;
        }
    }
}());
