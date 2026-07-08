<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2023 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model;

use Magento\Eav\Model\Entity\Attribute\Exception as AttributeException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\Newsletterpopup\Api\Data\HistoryInterface;
use Plumrocket\Newsletterpopup\Api\Data\HistoryInterfaceFactory;
use Plumrocket\Newsletterpopup\Api\HistoryRepositoryInterface;
use Plumrocket\Newsletterpopup\Model\ResourceModel\History as HistoryResource;

class HistoryRepository implements HistoryRepositoryInterface
{

    /**
     * @var array
     */
    private $instancesById = [];

    /**
     * @var \Plumrocket\Newsletterpopup\Api\Data\HistoryInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\History
     */
    private $resourceModel;

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\HistoryInterfaceFactory $popupFactory
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\History      $popupResource
     */
    public function __construct(
        HistoryInterfaceFactory $popupFactory,
        HistoryResource $popupResource
    ) {
        $this->modelFactory = $popupFactory;
        $this->resourceModel = $popupResource;
    }

    /**
     * @inheritdoc
     */
    public function save(HistoryInterface $history): HistoryInterface
    {
        try {
            unset($this->instancesById[$history->getId()]);
            $this->resourceModel->save($history);
        } catch (AttributeException $exception) {
            throw InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $history->getData($exception->getAttributeCode()),
                $exception
            );
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('The popup was unable to be saved. Please try again.'),
                $e
            );
        }

        unset($this->instancesById[$history->getId()]);
        return $this->getById((int) $history->getId());
    }

    /**
     * @inheritdoc
     */
    public function getById(int $historyId, bool $forceReload = false): HistoryInterface
    {
        if (! isset($this->instancesById[$historyId]) || $forceReload) {
            /** @var \Plumrocket\Newsletterpopup\Model\History|HistoryInterface $history */
            $history = $this->modelFactory->create();
            $this->resourceModel->load($history, $historyId);
            if (! $history->getId()) {
                throw new NoSuchEntityException(
                    __("The history that was requested doesn't exist. Verify the history and try again.")
                );
            }
            $this->instancesById[$history->getId()] = $history;
        }
        return $this->instancesById[$historyId];
    }
}
