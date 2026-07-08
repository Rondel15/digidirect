<?php

namespace Digidirect\ProductOverlay\Api;

use Digidirect\ProductOverlay\Api\Data\OverlayInterface;
use Digidirect\ProductOverlay\Api\Data\OverlaySearchResultsInterface;

/**
 * Overlay CRUD interface.
 * @api
 */
interface OverlayRepositoryInterface
{
    /**
     * Save overlay.
     *
     * @param \Digidirect\ProductOverlay\Api\Data\OverlayInterface $overlay
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(OverlayInterface $overlay);

    /**
     * Retrieve overlay.
     *
     * @param int $overlayId
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($overlayId);

    /**
     * Retrieve overlays matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Digidirect\ProductOverlay\Api\Data\OverlaySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete overlay.
     *
     * @param \Digidirect\ProductOverlay\Api\Data\OverlayInterface $overlay
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(OverlayInterface $overlay);

    /**
     * Delete overlay by ID.
     *
     * @param int $overlayId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($overlayId);
}
