<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2023 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface;
use Plumrocket\Newsletterpopup\Api\PopupThemeRepositoryInterface;

class Repository implements PopupThemeRepositoryInterface
{

    /**
     * @var \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface[]
     */
    private $instancesById = [];

    /**
     * @var \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme
     */
    private $resourceModel;

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterfaceFactory $popupThemeFactory
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme     $popupThemeResource
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterfaceFactory $popupThemeFactory,
        \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme $popupThemeResource
    ) {
        $this->modelFactory = $popupThemeFactory;
        $this->resourceModel = $popupThemeResource;
    }

    /**
     * @inheritdoc
     */
    public function save(PopupThemeInterface $popupTheme): PopupThemeInterface
    {
        try {
            unset($this->instancesById[$popupTheme->getId()]);
            $this->resourceModel->save($popupTheme);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('The popup was unable to be saved. Please try again.'),
                $e
            );
        }
        unset($this->instancesById[$popupTheme->getId()]);
        return $this->getById((int) $popupTheme->getId());
    }

    /**
     * @inheritdoc
     */
    public function getById(int $popupThemeId, bool $forceReload = false): PopupThemeInterface
    {
        if (! isset($this->instancesById[$popupThemeId]) || $forceReload) {
            /** @var \Plumrocket\Newsletterpopup\Model\Popup\Theme|PopupThemeInterface $popupTheme */
            $popupTheme = $this->modelFactory->create();
            $this->resourceModel->load($popupTheme, $popupThemeId);
            if (! $popupTheme->getId()) {
                throw NoSuchEntityException::singleField('id', $popupThemeId);
            }
            $this->instancesById[$popupTheme->getId()] = $popupTheme;
        }
        return $this->instancesById[$popupThemeId];
    }
}
