<?php
/**
 * ADOBE CONFIDENTIAL
 * Copyright 2024 Adobe
 * All Rights Reserved.
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

namespace Magento\CompanyRelation\Adminhtml\Company\Controller\Index;

use Magento\Company\Controller\Adminhtml\Index\MassUpdateSettings;
use Magento\CompanyRelation\Api\Data\RelationInterface;
use Magento\CompanyRelation\Test\Fixture\AssignRelations;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\Data\GroupInterface as CustomerGroupInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Company\Test\Fixture\CustomerGroup as CustomerGroupFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;
use Magento\User\Test\Fixture\User as AdminUserFixture;

/**
 * Test for mass company update settings controller, calling from company edit page.
 * @see \Magento\Company\Controller\Adminhtml\Index\MassUpdateSettings
 */
class MassUpdateSettingsTest extends AbstractBackendController
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var string
     */
    protected $resource = MassUpdateSettings::ADMIN_RESOURCE;

    /**
     * @var string
     */
    protected $uri = 'backend/company/index/massUpdateSettings';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->companyRepository = $this->objectManager->get(CompanyRepositoryInterface::class);

        parent::setUp();
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(
            CustomerGroupFixture::class,
            as: 'customerGroup'
        ),
        DataFixture(CustomerFixture::class, as: 'parentCompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'assignedCompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'regularCompanyAdmin'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser'),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Parent Company',
                CompanyInterface::SUPER_USER_ID => '$parentCompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'parentCompany'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Assigned Company',
                CompanyInterface::SUPER_USER_ID => '$assignedCompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'assignedCompany'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Regular Company',
                CompanyInterface::SUPER_USER_ID => '$regularCompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'regularCompany'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID => '$parentCompany.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$assignedCompany.id$'],
                ],
            ],
        ),
    ]
    /**
     * @param callable $companyIdResolver
     * @dataProvider companyIdDataProvider
     */
    public function testOnlyCompaniesWithinTheRelationGetUpdatedWhenSelectingAllWithinTheGrid(
        callable $companyIdResolver
    ) {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parentCompany');

        /** @var CompanyInterface $assignedCompany */
        $assignedCompany = $this->fixtures->get('assignedCompany');

        /** @var CompanyInterface $regularCompany */
        $regularCompany = $this->fixtures->get('regularCompany');

        /** @var CustomerGroupInterface $customerGroup */
        $customerGroup = $this->fixtures->get('customerGroup');

        // assert that companies' initial customer group id is not $customerGroup
        $this->assertNotEquals($customerGroup->getId(), $parentCompany->getCustomerGroupId());
        $this->assertNotEquals($customerGroup->getId(), $assignedCompany->getCustomerGroupId());
        $this->assertNotEquals($customerGroup->getId(), $regularCompany->getCustomerGroupId());

        $params = [
            MassActionFilter::EXCLUDED_PARAM => 'false',
            'selectedCount' => 2,
            'namespace' => 'company_hierarchy_listing',
            'filters' => [
                'placeholder' => true,
            ],
            'entity_id' => $companyIdResolver(), // making request from this company's edit page
            'customer_group_id' => $customerGroup->getId(),
        ];

        $request = $this->getRequest();
        $request->setParams($params);
        $request->setMethod($this->httpMethod);

        $this->dispatch($this->uri);

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $this->assertEquals(
            [
                'success' => true,
                'messages' => [
                    0 => [
                        'type' => 'success',
                        'text' => (string) __('Successfully changed settings for %1 companies.', 2),
                    ],
                ],
            ],
            json_decode($this->getResponse()->getContent(), true)
        );

        // refresh the companies
        $parentCompany = $this->companyRepository->get($parentCompany->getId());
        $assignedCompany = $this->companyRepository->get($assignedCompany->getId());
        $regularCompany = $this->companyRepository->get($regularCompany->getId());

        // assert that companies' customer group id is $customerGroup
        $this->assertEquals($customerGroup->getId(), $parentCompany->getCustomerGroupId());
        $this->assertEquals($customerGroup->getId(), $assignedCompany->getCustomerGroupId());

        // assert that regular company was unaffected in the update, despite selecting all within the grid
        $this->assertNotEquals($customerGroup->getId(), $regularCompany->getCustomerGroupId());
    }

    public static function companyIdDataProvider()
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        return [
            'parent company id' => [
                fn () => $fixtures->get('parentCompany')->getId(),
            ],
            'assigned company id' => [
                fn () => $fixtures->get('assignedCompany')->getId(),
            ],
        ];
    }
}
