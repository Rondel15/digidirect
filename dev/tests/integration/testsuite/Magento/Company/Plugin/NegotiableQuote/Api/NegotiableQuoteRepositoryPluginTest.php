<?php
/**
 * ADOBE CONFIDENTIAL
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
declare(strict_types=1);

namespace Magento\Company\Plugin\NegotiableQuote\Api;

use Magento\Backend\Model\Auth\Session;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\CompanyQuote\Model\CompanyQuoteLinkFactory;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\CompanyQuote\Model\ResourceModel\CompanyQuoteLink;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteDraftManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Test\Fixture\Group;
use Magento\Store\Test\Fixture\Store;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test coverage for quote extension company id after negotiable quote request
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NegotiableQuoteRepositoryPluginTest extends TestCase
{
    /**
     * @var NegotiableQuoteDraftManagement
     */
    private $negotiableQuoteDraftManagement;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Quote|null
     */
    private ?Quote $testQuote = null;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->negotiableQuoteDraftManagement =
            $this->objectManager->create(NegotiableQuoteDraftManagementInterface::class);
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_WEBSITE),
        DataFixture(Group::class, as: 'group'),
        DataFixture(Store::class, ['store_group_id' => '$group.id$',], 'store'),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SUPER_USER_ID => '$customer_a.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
            ],
            'company_a'
        ),
        DataFixture(Customer::class, as: 'customer_ab'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SUPER_USER_ID => '$customer_ab.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
            ],
            'company_b'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
    ]
    public function testCreateDraftByAdmin()
    {
        $user = DataFixtureStorageManager::getStorage()->get('user');
        $customerId = (int)DataFixtureStorageManager::getStorage()->get('customer_ab')->getId();
        $companyId = (int)FixtureManager::getStorage()->get('company_b')->getId();
        $session = $this->objectManager->get(Session::class);
        $this->testQuote = $this->objectManager->get(Quote::class);

        $session->setUser($user);
        $quoteId = $this->negotiableQuoteDraftManagement->createDraftByAdmin($customerId);
        $this->testQuote->loadByIdWithoutStore($quoteId);

        //assert Company Customer Link database
        $companyQuoteLink = Bootstrap::getObjectManager()->get(CompanyQuoteLinkFactory::class)
            ->create();
        Bootstrap::getObjectManager()->get(CompanyQuoteLink::class)
            ->load($companyQuoteLink, $quoteId, 'quote_id');
        $this->assertEquals($companyId, $companyQuoteLink->getData('company_id'));

        //assert Company Id in quote extension attribute
        $quote = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class)->get($quoteId);
        $this->assertEquals(
            $companyId,
            $quote->getExtensionAttributes()->getCompanyId()
        );
    }

    protected function tearDown(): void
    {
        /** removal of the quote created in the test */
        if ($this->testQuote) {
            $cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
            $cartRepository->delete($this->testQuote);
            $this->testQuote = null;
        }
        parent::tearDown();
    }
}
