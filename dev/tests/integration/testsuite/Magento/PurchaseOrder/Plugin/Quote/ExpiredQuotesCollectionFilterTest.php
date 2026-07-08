<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PurchaseOrder\Plugin\Quote;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Collection\ExpiredQuotesCollection;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Fixture\Config;
use PHPUnit\Framework\TestCase;

class ExpiredQuotesCollectionFilterTest extends TestCase
{
    /**
     * @var ExpiredQuotesCollection
     */
    private $expiredQuotesCollection;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = ObjectManager::getInstance();
        $this->expiredQuotesCollection = $objectManager->get(ExpiredQuotesCollection::class);
        $this->storeRepository = $objectManager->get(StoreRepositoryInterface::class);
        //default store
        $this->store = $this->storeRepository->get('default');
    }

    #[
        Config('btob/website_configuration/purchaseorder_enabled', '1'),
    ]
    public function testFilterExists()
    {
        $collection = $this->expiredQuotesCollection->getExpiredQuotes($this->store);
        $this->assertStringContainsStringIgnoringCase(
            'po.quote_id = main_table.entity_id',
            (string)$collection->getSelect()
        );
    }

    #[
        Config('btob/website_configuration/purchaseorder_enabled', '0'),
    ]
    public function testFilterNotExistsIfPurchaseOrderDisabled()
    {
        $collection = $this->expiredQuotesCollection->getExpiredQuotes($this->store);
        $this->assertStringNotContainsStringIgnoringCase(
            'po.quote_id = main_table.entity_id',
            (string)$collection->getSelect()
        );
    }
}
