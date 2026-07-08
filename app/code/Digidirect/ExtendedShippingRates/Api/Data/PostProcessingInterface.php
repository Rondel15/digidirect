<?php
namespace Digidirect\ExtendedShippingRates\Api\Data;

interface PostProcessingInterface
{
    const POST_PROCESSING = 'post_processing';

    /**
     * Get Post Processing
     *
     * @return string|null
     */
    public function getPostProcessing();

    /**
     * Set Post Processing
     *
     * @param bool $isPostProcessingRule
     * @return \Digidirect\ExtendedShippingRates\Api\Data\PostProcessingInterface
     */
    public function setPostProcessing($isPostProcessingRule);
}
