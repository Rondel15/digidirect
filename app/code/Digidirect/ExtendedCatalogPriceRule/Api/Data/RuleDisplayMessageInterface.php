<?php
namespace Digidirect\ExtendedCatalogPriceRule\Api\Data;

/**
 * Interface RuleDisplayMessageInterface
 * @package Digidirect\ExtendedCatalogPriceRule\Api\Data
 */
interface RuleDisplayMessageInterface
{
    const ACTION_CODE = 'show_msg';

    const PLP_LABEL = 'plp_label';
    const PDP_DESCRIPTION = 'pdp_description';
    const URL_PROMOTION = 'url_promotion';

    /**
     * @return string
     */
    public function getPlpLabel();

    /**
     * @param string $text
     * @return $this
     */
    public function setPlpLabel($text);

    /**
     * @return string
     */
    public function getPdpDescription();

    /**
     * @param string $text
     * @return $this
     */
    public function setPdpDescription($text);

    /**
     * @return string
     */
    public function getUrlPromotion();

    /**
     * @param string $url
     * @return $this
     */
    public function setUrlPromotion($url);
}
