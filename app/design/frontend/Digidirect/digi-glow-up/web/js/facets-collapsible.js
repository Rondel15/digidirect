define([], function () {
  'use strict';

  function initCollapsibleFacets() {

    const panels = document.querySelectorAll('.ais-Panel');

    panels.forEach(function (panel, index) {
      if (panel.dataset.collapsibleInit) return;
      panel.dataset.collapsibleInit = '1';

      var header = panel.querySelector('.ais-Panel-header');
      var body = panel.querySelector('.ais-Panel-body');
      if (!header || !body) return;

      body.classList.add('facet-body');
      
      // Collapse all except the first
      if (index !== 0) {
        body.classList.add('collapsed')
        header.classList.add('collapsed')
      }

      header.addEventListener('click', () => {
        body.classList.toggle('collapsed');
        header.classList.toggle('collapsed'); // add this line for arrow rotation
      });
    });
  }

  // Run after DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCollapsibleFacets);
  } else {
    initCollapsibleFacets();
  }

  // Watch for Algolia re-render
  const obs = new MutationObserver(initCollapsibleFacets);
  obs.observe(document.body, { childList: true, subtree: true });

  // Export for RequireJS test
  return {
    init: initCollapsibleFacets
  };
});
