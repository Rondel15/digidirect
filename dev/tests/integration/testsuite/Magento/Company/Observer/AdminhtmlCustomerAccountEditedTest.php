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

namespace Magento\Company\Observer;

use Magento\Company\Test\Fixture\AssignCompany as AssignCompanyFixture;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\User\Test\Fixture\User;

/**
 * Tests for adminhtml_customer_save_after event via backend/customer/index/save controller.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminhtmlCustomerAccountEditedTest extends AbstractBackendController
{
    /**
     * @var string
     */
    private $baseControllerUrl = 'backend/customer/index/';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
    }

    #[
        DataFixture(CustomerFixture::class, as: 'customer_a'),
        DataFixture(CustomerFixture::class, as: 'customer_b'),
        DataFixture(CustomerFixture::class, as: 'customer_c'),
        DataFixture(CustomerFixture::class, as: 'customer_abc'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            CompanyFixture::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_a.id$',

            ],
            'company_a'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_b.id$'
            ],
            'company_b'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_c.id$'
            ],
            'company_c'
        ),
        DataFixture(
            AssignCompanyFixture::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_abc.id$'
            ]
        ),
        DataFixture(
            AssignCompanyFixture::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_abc.id$',
            ]
        ),
        DataFixture(
            AssignCompanyFixture::class,
            [
                'company_id' => '$company_c.id$',
                'customer_id' => '$customer_abc.id$',
            ]
        ),
    ]
    /**
     * Test customer status after removing all companies the customer was assigned to
     *
     * @dataProvider updateCustomerProvider
     */
    public function testCustomerStatusAfterUnassigningAllCompanies(bool $status): void
    {
        $customerId = (int)DataFixtureStorageManager::getStorage()->get('customer_abc')->getId();
        /** @var CustomerData $customerData */
        $customerData = $this->customerRepository->getById($customerId);
        $this->assertTrue((bool) $customerData->getExtensionAttributes()->getCompanyAttributes()->getStatus());
        $postData = [
            'customer' => [
                'status' => (int)$status,
                'company_ids' => []
            ]
        ];
        $postData['customer']['entity_id'] = $customerId;
        $params = ['back' => true];

        $this->dispatchCustomerSave($postData, $params);
        $customerData = $this->customerRepository->getById($customerId);
        if ($status) {
            $this->assertTrue((bool)$customerData->getExtensionAttributes()->getCompanyAttributes()->getStatus());
        } else {
            $this->assertFalse((bool)$customerData->getExtensionAttributes()->getCompanyAttributes()->getStatus());
        }
    }

    /**
     * update customer provider
     *
     * @return array
     */
    public function updateCustomerProvider(): array
    {
        return [
            'customer_active' => ['status' => true],
            'customer_inactive' => ['status' => false],
        ];
    }

    /**
     * Create or update customer using backend/customer/index/save action.
     *
     * @param array $postData
     * @param array $params
     * @return void
     */
    private function dispatchCustomerSave(array $postData, array $params = []): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        if (!empty($params)) {
            $this->getRequest()->setParams($params);
        }
        $this->dispatch($this->baseControllerUrl . 'save');
    }
}
