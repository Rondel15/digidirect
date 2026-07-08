<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

use Plumrocket\Newsletterpopup\Model\IntegrationInterface;

/**
 * Class IntegrationRepository
 */
class IntegrationRepository implements \Plumrocket\Newsletterpopup\Model\IntegrationRepositoryInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var IntegrationIdentifierListInterface
     */
    private $integrationIdentifierList;

    /**
     * IntegrationRepository constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param IntegrationIdentifierListInterface $integrationIdentifierList
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Plumrocket\Newsletterpopup\Model\IntegrationIdentifierListInterface $integrationIdentifierList
    ) {
        $this->objectManager = $objectManager;
        $this->integrationIdentifierList = $integrationIdentifierList;
    }

    /**
     * @param $integrationId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function checkId($integrationId)
    {
        if (! $this->integrationIdentifierList->isValid($integrationId)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid integration identifier %1.', $integrationId)
            );
        }

        return $this;
    }

    /**
     * Retrieve cached object instance
     *
     * @param $integrationId
     * @return \Plumrocket\Newsletterpopup\Model\IntegrationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($integrationId)
    {
        $this->checkId($integrationId);

        $className = $this->integrationIdentifierList->getIntegrationClass($integrationId);
        $model = $this->objectManager->get($className);

        if (! $model instanceof IntegrationInterface) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Integration model %1 must implement %2 interface.', $className, IntegrationInterface::class)
            );
        }

        return $model;
    }

    /**
     * Retrieve integrations list
     *
     * @return \Plumrocket\Newsletterpopup\Model\IntegrationInterface[]
     */
    public function getList()
    {
        $models = [];

        foreach ($this->integrationIdentifierList->getIntegrationIdentifiers() as $integrationId) {
            $models[$integrationId] = $this->get($integrationId);
        }

        return $models;
    }
}
