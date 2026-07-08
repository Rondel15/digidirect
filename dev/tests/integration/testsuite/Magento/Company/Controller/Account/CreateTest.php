<?php

/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2020 Adobe
 *  All Rights Reserved.
 *
 *  NOTICE: All information contained herein is, and remains
 *  the property of Adobe and its suppliers, if any. The intellectual
 *  and technical concepts contained herein are proprietary to Adobe
 *  and its suppliers and are protected by all applicable intellectual
 *  property laws, including trade secret and copyright laws.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from Adobe.
 *  ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Company\Controller\Account;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test for CreatePost controller.
 *
 * @see \Magento\Company\Controller\Account\CreatePost
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class CreateTest extends AbstractController
{
    private const XML_PATH_COMPANY_ACTIVE = 'btob/website_configuration/company_active';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /** @var Page */
    private $page;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->_objectManager->get(MutableScopeConfigInterface::class);
        $config->setValue(self::XML_PATH_COMPANY_ACTIVE, 1, ScopeInterface::SCOPE_WEBSITE);
        $this->session = $this->_objectManager->get(Session::class);
        $this->customerRepository = $this->_objectManager->get(CustomerRepository::class);
        $this->page = $this->_objectManager->get(PageFactory::class)->create();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $config = $this->_objectManager->get(MutableScopeConfigInterface::class);
        $config->setValue(self::XML_PATH_COMPANY_ACTIVE, 0, ScopeInterface::SCOPE_WEBSITE);
        $this->session = null;
        $this->customerRepository = null;
        parent::tearDown();
    }

    /**
     * Try to open company create form when logged as company admin customer
     *
     * @magentoDataFixture Magento/Company/_files/company_with_structure.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testCreate(): void
    {
        $adminCustomer = $this->customerRepository->get('john.doe@example.com');

        $this->session->loginById($adminCustomer->getId());
        try {
            $this->dispatch('company/account/create');

            $this->assertEquals('403', $this->getResponse()->getHttpResponseCode());
        } finally {
            $this->session->logout();
        }
    }

    /**
     * Validate work phone number exists in the company form.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store btob/website_configuration/company_active 1
     * @magentoConfigFixture default_store company/general/allow_company_registration 1
     *
     * @return void
     */
    public function testValidateWorkPhoneNumberExistsInCompanyForm(): void
    {
        $this->dispatch('company/account/create');
        $this->assertStringContainsString(
            'input type="text" name="company[company_customer_telephone]"',
            $this->getResponse()->getContent(),
            'Work phone field is existing in company form.'
        );
    }
}
