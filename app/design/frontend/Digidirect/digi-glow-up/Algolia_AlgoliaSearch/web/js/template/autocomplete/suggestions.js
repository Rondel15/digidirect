define([], function () {
    return {
        getNoResultHtml: function ({html}) {
            return html`<p>${algoliaConfig.translations.noResults}</p>`;
        },

        getHeaderHtml: function ({html}) {
            return html`<p>Popular Searches</p>`;
        },

        getItemHtml: function ({item, components, html}) {
            const itemQuery = (item._highlightResult?.query?.value)
                ? components.Highlight({ hit: item, attribute: "query" })
                : item.query;

            return html`<svg xmlns="http://www.w3.org/2000/svg" width="21" height="20" viewBox="0 0 21 20" fill="none">
<path d="M17.5 17L13.7223 13.2156M15.8158 8.1579C15.8158 10.0563 15.0617 11.8769 13.7193 13.2193C12.3769 14.5617 10.5563 15.3158 8.6579 15.3158C6.7595 15.3158 4.93886 14.5617 3.5965 13.2193C2.25413 11.8769 1.5 10.0563 1.5 8.1579C1.5 6.2595 2.25413 4.43886 3.5965 3.0965C4.93886 1.75413 6.7595 1 8.6579 1C10.5563 1 12.3769 1.75413 13.7193 3.0965C15.0617 4.43886 15.8158 6.2595 15.8158 8.1579Z" stroke="#676767" stroke-width="1.5" stroke-linecap="round"/></svg><a class="aa-ItemLink algolia-suggestions algoliasearch-autocomplete-hit"
                           href="${algoliaConfig.resultPageUrl}?q=${encodeURIComponent(item.query)}"
                           data-objectId="${item.objectID}"
                           data-position="${item.position}"
                           data-index="${item.__autocomplete_indexName}"
                           data-queryId="${item.__autocomplete_queryID}">
                ${itemQuery}
            </a>`;
        },

        getFooterHtml: function ({html}) {
            return html`<div class="aa-SourceHeader"><p>Help</p></div>
<ul class="aa-List" role="listbox" aria-labelledby="autocomplete-0-label" id="autocomplete-0-list">
	<li class="aa-Item" id="autocomplete-0-item-0" role="option" aria-selected="false">
			<svg xmlns="http://www.w3.org/2000/svg" width="19" height="21" viewBox="0 0 19 21" fill="none">
<path d="M9.50405 11.6057C11.1027 11.6057 12.3987 10.3098 12.3987 8.71108C12.3987 7.1124 11.1027 5.81641 9.50405 5.81641C7.90536 5.81641 6.60938 7.1124 6.60938 8.71108C6.60938 10.3098 7.90536 11.6057 9.50405 11.6057Z" stroke="#676767" stroke-width="1.5"/>
<path d="M1.72522 7.02129C3.55295 -1.01328 15.4564 -1.004 17.2748 7.03057C18.3418 11.7437 15.41 15.7331 12.84 18.201C10.9752 20.0009 8.02485 20.0009 6.15073 18.201C3.59006 15.7331 0.658278 11.7344 1.72522 7.02129Z" stroke="#676767" stroke-width="1.5"/>
</svg>
		<a class="aa-ItemLink algolia-suggestions algoliasearch-autocomplete-hit" href="/store-locator">
			Find a Store
		</a>
	</li>
	<li class="aa-Item" id="autocomplete-0-item-1" role="option" aria-selected="false">
			<svg xmlns="http://www.w3.org/2000/svg" width="21" height="20" viewBox="0 0 21 20" fill="none">
<path d="M14.6641 15.3588H11.3307L7.62239 17.8254C7.07239 18.1921 6.33073 17.8004 6.33073 17.1338V15.3588C3.83073 15.3588 2.16406 13.6921 2.16406 11.1921V6.19206C2.16406 3.69206 3.83073 2.02539 6.33073 2.02539H14.6641C17.1641 2.02539 18.8307 3.69206 18.8307 6.19206V11.1921C18.8307 13.6921 17.1641 15.3588 14.6641 15.3588Z" stroke="#676767" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10.5005 9.4668V9.29183C10.5005 8.72516 10.8505 8.42515 11.2005 8.18348C11.5422 7.95015 11.8838 7.65016 11.8838 7.10016C11.8838 6.33349 11.2672 5.7168 10.5005 5.7168C9.73383 5.7168 9.11719 6.33349 9.11719 7.10016" stroke="#676767" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10.4989 11.4577H10.5064" stroke="#676767" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
		<a class="aa-ItemLink algolia-suggestions algoliasearch-autocomplete-hit" href="/faq">
			FAQs
		</a>
	</li>
</ul>`;
        }
    };
});
