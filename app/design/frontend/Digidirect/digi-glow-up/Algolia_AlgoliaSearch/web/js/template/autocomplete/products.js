define([], function () {
    return {

        ////////////////////
        //  Template API  //
        ////////////////////

        getNoResultHtml: function ({html}) {
            return html`<p>${algoliaConfig.translations.noResults}</p>`;
        },

        getHeaderHtml: function () {
            return "";
        },

        getItemHtml: function ({item, components, html}) {
            return html`<a class="algoliasearch-autocomplete-hit"
                           href="${item.url}"
                           data-objectId="${item.objectID}"
                           data-position="${item.position}"
                           data-index="${item.__autocomplete_indexName}"
                           data-queryId="${item.__autocomplete_queryID}">
                <div class="thumb"><img src="${item.thumbnail_url || ''}"/></div>
                <div class="info">
                    <div class="algoliasearch-autocomplete-name">
                        ${this.safeHighlight(components, item, "name")}
                    </div>
                    <div class="algoliasearch-autocomplete-category">
                        ${this.getColorHtml(item, components, html)}
                        ${this.getCategoriesHtml(item, components, html)}
                    </div>

                    ${this.getPricingHtml(item, html)}
                </div>
            </a>`;
        },

        getFooterHtml: function ({html, ...resultDetails}) {
            return html`<div id="autocomplete-products-footer">
                ${this.getFooterSearchLinks(html, resultDetails)}
            </div>`;
        },

        ////////////////////
        // Helper methods //
        ////////////////////

        getColorHtml: function(item, components, html) {
            const highlight = this.safeHighlight(components, item, "color");
            
            return highlight 
                ? html`<span class="color">color: ${highlight}</span>`
                : "";
        },

        getCategoriesHtml: function(item, components, html) {
            const highlight = this.safeHighlight(components, item, "categories_without_path", false);

            return highlight 
                ? html`<span>in ${highlight}</span>`
                : "";
        },

        getOriginalPriceHtml: (item, html, priceGroup) => {
            if (item['price'][algoliaConfig.currencyCode][priceGroup + '_original_formated'] == null) return "";

            return html`<span class="before_special"> ${item['price'][algoliaConfig.currencyCode][priceGroup + '_original_formated']} </span>`;
        },

        getTierPriceHtml: (item, html, priceGroup) => {
            if (item['price'][algoliaConfig.currencyCode][priceGroup + '_tier_formated'] == null) return "";

            return html`<span class="tier_price"> As low as <span class="tier_value">${item['price'][algoliaConfig.currencyCode][priceGroup + '_tier_formated']}</span></span>`;
        },

        getPricingHtml: function(item, html) {
            if (item['price'] == undefined) return "";

            const priceGroup =  algoliaConfig.priceGroup || 'default';
            
            const formatter = new Intl.NumberFormat('en-AU', {
                style: 'currency',
                currency: 'AUD',
                minimumFractionDigits: 2
            });
            
            //custom_final_price
            /*if (item['custom_final_price']) {
                //Set price to custom_final_price
                if ((item['custom_final_price'] < item['price']['AUD']['default']) && item['custom_final_price'] != 0) {
                    item.customFinalPrice =  formatter.format(item['custom_final_price']);
                    hasCustomFinalPrice = true;
                }

                //Set price to default
                //item.customFinalPrice =  formatter.format(item['price']['AUD']['default']);
                //hasCustomFinalPrice = false;
            }*/
            
            //wiser_price
            if (item['wiser_price']) {
                //Set price to wiser_price
                var wiserDiscount = item['price']['AUD']['default'] - item['wiser_price'];
                if ((item['wiser_price'] < item['price']['AUD']['default']) && item['wiser_price'] != 0) {
                    item.customFinalPrice =  formatter.format(item['wiser_price']);
                    return html `<div className="algoliasearch-autocomplete-price">
                            <span className="after_special custom_final_price">
                                ${formatter.format(item['wiser_price'])}
                            </span>
                        </div>`;
                } else {
                    item.customFinalPrice =  formatter.format(item['price']['AUD']['default']);
                }
            }
            
            if ((item['price']['AUD']['default'] < item['wiser_price']) || !item['wiser_price'] || item['wiser_price'] === null || item['wiser_price'] === undefined) {
                //programmed_promotion_price
                if (item['price']['AUD']['default_original_formated'] && item['price']['AUD']['default_original_formated'] !== "undefined") {
                    var defaultOriginalPrice = item['price']['AUD']['default_original_formated'];
                    defaultOriginalPrice = Number(defaultOriginalPrice.replace("$", "").replace(",", ""));

                    if (defaultOriginalPrice > item['price']['AUD']['default']) {
                        //Set price to custom_final_price
                        var priceDiscount = defaultOriginalPrice - item['price']['AUD']['default'];
                        /*item.defaultOriginalPrice = item['price']['AUD']['default_original_formated'];
                        item.customFinalPrice =  formatter.format(item['price']['AUD']['default']);
                        item.discount = formatter.format(priceDiscount);
                        item.hasCustomFinalPrice = true;
                        item.hasNoCustomFinalPrice = false;
                        item.customFinalPrice =  formatter.format(item['wiser_price']);*/
                        
                        return html `<div className="algoliasearch-autocomplete-price">
                            <span className="before_price promotional">
                                ${item['price']['AUD']['default_original_formated']}
                            </span>
                            <span className="after_special custom_final_price">
                                ${formatter.format(item['price']['AUD']['default'])}
                            </span>
                            <div className="discount">
                                SAVE ${formatter.format(priceDiscount)}
                            </div>
                        </div>`;
                    }
                }
            } else {
                //wiser_price
                if (item['wiser_price']) {

                    var basePrice;

                    //Set price to custom_final_price
                    var defaultOriginalPrice = item['price']['AUD']['default_original_formated'];

                    if (defaultOriginalPrice) {
                        defaultOriginalPrice = Number(defaultOriginalPrice.replace("$", "").replace(",", ""));
                        basePrice = defaultOriginalPrice;
                    } else {
                        basePrice = item['price']['AUD']['default'];
                    }

                    var wiserDiscount = basePrice - item['wiser_price'];
                    if ((item['wiser_price'] < item['price']['AUD']['default']) && item['wiser_price'] != 0) {
                        /*item.defaultOriginalPrice = item['price']['AUD']['default_original_formated'];
                        item.customFinalPrice =  formatter.format(item['wiser_price']);
                        item.discount = formatter.format(wiserDiscount);
                        item.hasCustomFinalPrice = true;
                        item.hasNoCustomFinalPrice = false;*/
                        return html `<div className="algoliasearch-autocomplete-price">
                            <span className="before_price wiser">
                                ${item['price']['AUD']['default_original_formated']}
                            </span>
                            <span className="after_special custom_final_price">
                                ${formatter.format(item['wiser_price'])}
                            </span>
                            <div className="discount">
                                SAVE ${formatter.format(wiserDiscount)}
                            </div>
                        </div>`;
                    }
                    //Set price to default
                    //item.customFinalPrice =  formatter.format(item['price']['AUD']['default']);
                    //item.hasCustomFinalPrice = false;
                    //item.hasNoCustomFinalPrice = true;
                }
            }
            
            return html `<div className="algoliasearch-autocomplete-price">
                    <span className="after_special ${item['price'][algoliaConfig.currencyCode][priceGroup + '_original_formated'] != null ? 'promotion' : ''}">
                        ${item['price'][algoliaConfig.currencyCode][priceGroup + '_formated']}
                    </span>
                </div>`;
        },

        getFooterSearchCategoryLinks: (html, resultDetails) => {
            if (resultDetails.allCategories == undefined || resultDetails.allCategories.length === 0) return "";

            return html ` ${algoliaConfig.translations.orIn}
                ${resultDetails.allCategories.map((list, index) =>
                    index === 0 ? html` <span><a href="${list.url}">${list.name}</a></span>` : html`, <span><a href="${list.url}">${list.name}</a></span>`
                )}
            `;
        },

        getFooterSearchLinks: function(html, resultDetails)  {
            if (resultDetails.nbHits === 0) return "";

            return html`${algoliaConfig.translations.seeIn} <span><a href="${resultDetails.allDepartmentsUrl}">${algoliaConfig.translations.allDepartments}</a></span> (${resultDetails.nbHits})
                ${this.getFooterSearchCategoryLinks(html, resultDetails)}
            `;
        },

        // TODO: Refactor to external lib
        safeHighlight: function(components, hit, attribute, strict = true) {
            const highlightResult = hit._highlightResult[attribute];

            if (!highlightResult) return '';

            if (strict
                &&
                (
                    (Array.isArray(highlightResult)) && !highlightResult.find(hit => hit.matchLevel !== 'none')
                    ||
                    highlightResult.value === ''
                )
            ) {
                return '';
            }

            try {
                return components.Highlight({ hit, attribute });
            } catch (e) {
                return '';
            }
        }

    };
});
