<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompanyRelation\Service\V1;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\Company;
use Magento\CompanyRelation\Api\Data\RelationInterface;
use Magento\CompanyRelation\Test\Fixture\AssignRelations;
use Magento\Customer\Test\Fixture\Customer;
use Magento\CompanyRelation\Model\RelationManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Test\Fixture\User;

/**
 * Tests company relation service WebAPI
 */
#[
    Config('btob/website_configuration/company_active', 1)
]
class UnassignRelationsTest extends WebapiAbstract
{
    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RelationManager
     */
    private $relationManager;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly('Functionality available in REST mode only.');
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->relationManager = $objectManager->get(RelationManager::class);
        parent::setUp();
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanyASuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanyASuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanyASuperUser.id$'
            ],
            as: 'parentCompany'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanyASuperUser.id$'
            ],
            as: 'childCompany'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parentCompany.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$childCompany.id$']
                ]
            ]
        ),
    ]
    public function testUnassigningChildFromParentCompany()
    {
        $parentCompanyId = (int) $this->fixtures->get('parentCompany')->getId();
        $childCompanyId = (int) $this->fixtures->get('childCompany')->getId();
        $this->assertNotEmpty($this->getRelationByParentId($parentCompanyId));
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/company/{$parentCompanyId}/relations/{$childCompanyId}",
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
        ];
        $this->_webApiCall($serviceInfo);
        $this->assertEmpty($this->getRelationByParentId($parentCompanyId));
    }

    #[
        DataFixture(Customer::class, as: 'companyASuperUser'),
        DataFixture(Customer::class, as: 'parentCompanyASuperUser'),
        DataFixture(Customer::class, as: 'childCompanyASuperUser'),
        DataFixture(Customer::class, as: 'parentCompanyBSuperUser'),
        DataFixture(Customer::class, as: 'childCompanyBSuperUser'),
        DataFixture(User::class, as: 'companySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$companySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$companyASuperUser.id$'
            ],
            as: 'companyA'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$companySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanyASuperUser.id$'
            ],
            as: 'parentCompanyA'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$companySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanyASuperUser.id$'
            ],
            as: 'childCompanyA'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parentCompanyA.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$childCompanyA.id$']
                ]
            ]
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$companySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanyBSuperUser.id$'
            ],
            as: 'parentCompanyB'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$companySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanyBSuperUser.id$'
            ],
            as: 'childCompanyB'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parentCompanyB.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$childCompanyB.id$']
                ]
            ]
        ),
    ]
    /**
     * @dataProvider unassignCompanyInvalidIdDataProvider
     */
    public function testUnassignCompanyInvalid($parentId, $companyId, $expectedMsg, $expectedParams = [])
    {
        $parentId = is_int($parentId) ? $parentId : (int) $this->fixtures->get($parentId)->getId();
        $companyId = is_int($companyId) ? $companyId : (int) $this->fixtures->get($companyId)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/company/{$parentId}/relations/{$companyId}",
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo);
            $this->fail("Request expected to fail with an error.");
        } catch (\Exception $e) {
            foreach ($expectedParams as $key => $param) {
                $expectedMsg['parameters'][$key] = $$param;
            }
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMsg, $errorObj);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    public function unassignCompanyInvalidIdDataProvider()
    {
        return [
            'regular_company_as_parent' => [
                'companyA',
                'childCompanyA',
                ['message' => 'Relationship between parent ID %parent_id and company ID %company_id does not exist.'],
                ['parent_id' => 'parentId', 'company_id' => 'companyId']
            ],
            'regular_company_as_child' => [
                'parentCompanyA',
                'companyA',
                ['message' => 'Relationship between parent ID %parent_id and company ID %company_id does not exist.'],
                ['parent_id' => 'parentId', 'company_id' => 'companyId']
            ],
            'invert_parent' => [
                'childCompanyA',
                'parentCompanyA',
                ['message' => 'Relationship between parent ID %parent_id and company ID %company_id does not exist.'],
                ['parent_id' => 'parentId', 'company_id' => 'companyId']
            ],
            'parent_child_mismatch' => [
                'parentCompanyA',
                'childCompanyB',
                ['message' => 'Relationship between parent ID %parent_id and company ID %company_id does not exist.'],
                ['parent_id' => 'parentId', 'company_id' => 'companyId']
            ],
            'nonexistent_parent' => [
                100500,
                'childCompanyA',
                [
                    'message' => 'No such entity with parent_id = %parent_id.',
                    'parameters' => ['parent_id' => 100500]
                ]
            ],
            'nonexistent_child' => [
                'parentCompanyA',
                100501,
                [
                    'message' => 'No such entity with company_id = %company_id.',
                    'parameters' => ['company_id' => 100501]
                ]
            ],
        ];
    }

    /**
     * @param int $parentId
     * @return RelationInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getRelationByParentId(int $parentId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('parent_id', $parentId)
            ->create();

        return $this->relationManager->getList($searchCriteria)->getItems();
    }

    /**
     * @dataProvider invalidHttpVerbsDataProvider
     * @return void
     */
    public function testInvalidHttpVerbs($method)
    {
        $parentCompanyId = 100500;
        $childCompanyId = 100501;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/company/{$parentCompanyId}/relations/{$childCompanyId}",
                'httpMethod' => $method,
            ],
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Request does not match any route.");
        $this->_webApiCall($serviceInfo);
    }

    public function invalidHttpVerbsDataProvider()
    {
        return [
            Request::HTTP_METHOD_GET => [Request::HTTP_METHOD_GET],
            Request::HTTP_METHOD_POST => [Request::HTTP_METHOD_POST],
            Request::HTTP_METHOD_PUT => [Request::HTTP_METHOD_PUT],
        ];
    }

    /**
     * @dataProvider invalidCompanyIdDataProvider
     * @return void
     */
    public function testInvalidCompanyId($parentCompanyId, $childCompanyId, $msg)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/company/{$parentCompanyId}/relations/{$childCompanyId}",
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($msg);
        $this->_webApiCall($serviceInfo);
    }

    public function invalidCompanyIdDataProvider()
    {
        return [
            'parentId_is_empty' => [
                '',
                100501,
                'The \"\" value\'s type is invalid. The \"int\" type was expected. Verify and try again.'
            ],
            'parentId_is_string' => [
                'companyA',
                100501,
                'The \"companyA\" value\'s type is invalid. The \"int\" type was expected. Verify and try again.'
            ],
            'companyId_is_empty' => [
                100500,
                '',
                'Request does not match any route.'
            ],
            'companyId_is_string' => [
                100500,
                'companyB',
                'The \"companyB\" value\'s type is invalid. The \"int\" type was expected. Verify and try again.'
            ],
        ];
    }
}
