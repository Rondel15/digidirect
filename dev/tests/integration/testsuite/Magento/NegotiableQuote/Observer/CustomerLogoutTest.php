<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
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
 * ************************************************************************
 */
declare(strict_types=1);

/** Temporarily moved to this module form Magento_Company in order to make the builds run because of the order change */
namespace Magento\NegotiableQuote\Observer;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\CompanyContextInterface;
use Magento\Company\Test\Fixture\AssignCustomer;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Model\Session;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\User\Test\Fixture\User;

#[
    AppArea('frontend'),
    DataFixture(Customer::class, as: 'company_user_a'),
    DataFixture(Customer::class, as: 'company_user_b'),
    DataFixture(Customer::class, as: 'company_user_c'),
    DataFixture(User::class, as: 'sales_rep'),
    DataFixture(
        Company::class,
        [
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
            CompanyInterface::SUPER_USER_ID => '$company_user_a.id$'
        ],
        'company_a'
    ),
    DataFixture(
        Company::class,
        [
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
            CompanyInterface::SUPER_USER_ID => '$company_user_b.id$'
        ],
        'company_b'
    ),
    DataFixture(
        AssignCustomer::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$company_user_c.id$'
        ]
    )
]
class CustomerLogoutTest extends AbstractController
{
    private const URI = 'customer/account/logout';
    private const XML_CONFIG_PATH = 'btob/website_configuration/company_active';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CompanyContextInterface
     */
    private $companySession;

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $scopeConfig = $this->_objectManager->get(MutableScopeConfigInterface::class);
        $scopeConfig->setValue(self::XML_CONFIG_PATH, '1', ScopeInterface::SCOPE_WEBSITE);
        $scopeConfig->setValue(self::XML_CONFIG_PATH, '1', ScopeInterface::SCOPE_STORE);
        $this->session = $this->_objectManager->get(Session::class);
        $this->companySession = $this->_objectManager->get(CompanyContextInterface::class);
        $this->httpContext = $this->_objectManager->get(Context::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->session->logout();
        parent::tearDown();
    }

    /**
     * @return void
     * @dataProvider dataTestLogout
     */
    public function testLogout(string $httpMethod): void
    {
        $customerId = (int)DataFixtureStorageManager::getStorage()->get('company_user_c')->getId();
        $companyId = (int)DataFixtureStorageManager::getStorage()->get('company_a')->getId();
        $this->session->loginById($customerId);
        self::assertEquals($companyId, $this->companySession->getCompanyId());
        self::assertEquals($companyId, $this->httpContext->getValue(CompanyContextInterface::CONTEXT_COMPANY_ID));

        $this->getRequest()->setMethod($httpMethod);
        $this->dispatch(self::URI);
        $this->assertRedirect($this->stringContains('customer/account/logoutSuccess'));
        self::assertEquals(0, $this->companySession->getCompanyId());
        self::assertEquals(0, $this->httpContext->getValue(CompanyContextInterface::CONTEXT_COMPANY_ID));
    }

    /**
     * @return array
     */
    public function dataTestLogout(): array
    {
        return [
            Http::METHOD_GET => [Http::METHOD_GET],
            Http::METHOD_POST => [Http::METHOD_POST],
        ];
    }
}
