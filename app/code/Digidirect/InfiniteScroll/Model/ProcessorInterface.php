<?php
namespace Digidirect\InfiniteScroll\Model;

interface ProcessorInterface
{
    /**
     * Method responsible for the ajax content generation
     *
     * @return string
     */
    public function process();

    /**
     * Check if this processor is enabled for infinite scroll processing
     * 
     * @return boolean
     */
    public function isEnabled();

    /**
     * Returns url for the next page or false if there is no next page
     *
     * @return string|bool
     */
    public function getNextPageUrl();

    /**
     * Returns action type for current processor (click or scroll)
     * 
     * @return int
     */
    public function getActionType();

    /**
     * Returns number of entities for current processor
     * 
     * @return int
     */
    public function getLimit();

    /**
     * Returns collection total size
     *
     * @return int
     */
    public function getTotalSize();

    /**
     * Returns number of loaded entities
     *
     * @return int
     */
    public function getCurrentSize();
}
