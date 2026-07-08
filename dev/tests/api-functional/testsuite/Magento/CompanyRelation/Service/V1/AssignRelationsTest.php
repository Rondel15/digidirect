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
use Magento\CompanyRelation\Model\RelationManager;
use Magento\CompanyRelation\Test\Fixture\AssignRelations;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Webapi\Exception as WebapiException;
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
class AssignRelationsTest extends WebapiAbstract
{
    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @var RelationManager
     */
    private $relationManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly('Functionality available in REST mode only.');

        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->relationManager = $objectManager->get(RelationManager::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);

        parent::setUp();
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$'
            ],
            as: 'child'
        ),
    ]
    public function testCreateRelationWithRegularCompanies()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        /** @var CompanyInterface $childCompany */
        $childCompany = $this->fixtures->get('child');

        $this->makeWebApiCall((int) $parentCompany->getId(), (int) $childCompany->getId());

        $relationsToChildCompany = $this->getRelationsByChildId(
            (int) $childCompany->getId()
        );
        $this->assertCount(1, $relationsToChildCompany);

        $relationToChildCompany = $relationsToChildCompany[0];
        $this->assertEquals(
            $childCompany->getId(),
            $relationToChildCompany->getCompanyId()
        );

        $this->assertEquals(
            $parentCompany->getId(),
            $relationToChildCompany->getParentId()
        );

        $relationsToParentCompany = $this->getRelationsByParentId(
            (int) $parentCompany->getId()
        );
        $this->assertCount(1, $relationsToParentCompany);

        $relationToParentCompany = $relationsToParentCompany[0];
        $this->assertEquals(
            $childCompany->getId(),
            $relationToParentCompany->getCompanyId()
        );

        $this->assertEquals(
            $parentCompany->getId(),
            $relationToParentCompany->getParentId()
        );
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'child2CompanySuperUser'),
        DataFixture(User::class, as: 'child2CompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$'
            ],
            as: 'child'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$child2CompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$child2CompanySuperUser.id$'
            ],
            as: 'child2'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child.id$']
                ]
            ]
        ),
    ]
    public function testParentCanHaveMultipleChildren()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        /** @var CompanyInterface $childCompany2 */
        $childCompany2 = $this->fixtures->get('child2');

        $relationsToParentCompany = $this->getRelationsByParentId(
            (int) $parentCompany->getId()
        );
        $this->assertCount(1, $relationsToParentCompany);

        // create another relation, this time between parent -> child2
        $this->makeWebApiCall((int) $parentCompany->getId(), (int) $childCompany2->getId());

        $relationsToChildCompany2 = $this->getRelationsByChildId(
            (int) $childCompany2->getId()
        );
        $this->assertCount(1, $relationsToChildCompany2);

        $relationToChildCompany2 = $relationsToChildCompany2[0];
        $this->assertEquals(
            $childCompany2->getId(),
            $relationToChildCompany2->getCompanyId()
        );

        $this->assertEquals(
            $parentCompany->getId(),
            $relationToChildCompany2->getParentId()
        );

        $relationsToParentCompany = $this->getRelationsByParentId(
            (int) $parentCompany->getId()
        );
        $this->assertCount(2, $relationsToParentCompany);
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        )
    ]
    public function testCannotCreateRelationWithoutPassingChildIds()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        $this->makeWebApiCallAndExpectException(
            [
                'message' => (string) __(
                    'Cannot assign company to parent company ID %parent_id. No company_id specified.',
                    ['parent_id' => $parentCompany->getId()]
                ),
                'code' => WebapiException::HTTP_BAD_REQUEST
            ],
            (int) $parentCompany->getId()
        );

        $this->assertEmpty(
            $this->getRelationsByParentId((int) $parentCompany->getId())
        );
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        ),
    ]
    public function testCannotCreateRelationWithNonExistentChildId()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        $nonExistentChildCompanyId = -1;

        $this->makeWebApiCallAndExpectException(
            [
                'message' => (string) __('Cannot create some company relationships.'),
                'errors' => [
                    (string) __(
                        'No such entity with company_id = %company_id.',
                        ['company_id' => $nonExistentChildCompanyId]
                    )
                ],
                'code' => WebapiException::HTTP_BAD_REQUEST
            ],
            (int) $parentCompany->getId(),
            $nonExistentChildCompanyId
        );

        $this->assertEmpty(
            $this->getRelationsByParentId((int) $parentCompany->getId())
        );
    }

    #[
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$'
            ],
            as: 'child'
        ),
    ]
    public function testCannotCreateRelationWithNonExistentParentId()
    {
        /** @var CompanyInterface $childCompany */
        $childCompany = $this->fixtures->get('child');

        $nonExistentParentCompanyId = -1;

        $this->makeWebApiCallAndExpectException(
            [
                'message' => (string) __(
                    'No such entity with parent_id = %parent_id.',
                    ['parent_id' => $nonExistentParentCompanyId]
                ),
                'code' => WebapiException::HTTP_NOT_FOUND
            ],
            $nonExistentParentCompanyId,
            (int) $childCompany->getId()
        );

        $this->assertEmpty(
            $this->getRelationsByChildId((int) $childCompany->getId())
        );
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'parent2CompanySuperUser'),
        DataFixture(User::class, as: 'parent2CompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parent2CompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parent2CompanySuperUser.id$'
            ],
            as: 'parent2'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$'
            ],
            as: 'child'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child.id$']
                ]
            ]
        ),
    ]
    public function testChildCompanyCannotHaveRelationWithMoreThanOneParentCompany()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        /** @var CompanyInterface $childCompany */
        $childCompany = $this->fixtures->get('child');

        /** @var CompanyInterface $parent2Company */
        $parent2Company = $this->fixtures->get('parent2');

        $relationsToChildCompany = $this->getRelationsByChildId(
            (int) $childCompany->getId()
        );

        $this->assertCount(1, $relationsToChildCompany);

        // attempt to create another relation, this time between parent2 -> child; exception should be thrown
        $this->makeWebApiCallAndExpectException(
            [
                'message' => (string) __('Cannot create some company relationships.'),
                'errors' => [
                    (string) __(
                        'A company cannot have multiple parents.' .
                        ' Company ID %company_id is already assigned to a different parent company.',
                        ['company_id' => $childCompany->getId()]
                    ),
                ],
                'code' => WebapiException::HTTP_BAD_REQUEST
            ],
            (int) $parent2Company->getId(),
            (int) $childCompany->getId()
        );

        $relationsToChildCompany = $this->getRelationsByChildId(
            (int) $childCompany->getId()
        );

        $this->assertCount(1, $relationsToChildCompany);

        $this->assertEquals(
            $parentCompany->getId(),
            $relationsToChildCompany[0]->getParentId()
        );
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$'
            ],
            as: 'child'
        ),
    ]
    public function testAssignCallWithSamePayloadCanBeCalledConsecutivelyWithoutError()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        /** @var CompanyInterface $childCompany */
        $childCompany = $this->fixtures->get('child');

        // create relation between parent and child
        $this->makeWebApiCall((int) $parentCompany->getId(), (int) $childCompany->getId());

        // attempt to create the same relation; no exception should be thrown
        $this->makeWebApiCall((int) $parentCompany->getId(), (int) $childCompany->getId());

        // assert relation is left intact in DB
        $relationsToParentCompany = $this->getRelationsByParentId(
            (int) $parentCompany->getId()
        );

        $this->assertCount(1, $relationsToParentCompany);

        $relationToParentCompany = $relationsToParentCompany[0];

        $this->assertEquals(
            $parentCompany->getId(),
            $relationToParentCompany->getParentId()
        );

        $this->assertEquals(
            $parentCompany->getId(),
            $relationToParentCompany->getParentId()
        );

        $this->assertEquals(
            $childCompany->getId(),
            $relationToParentCompany->getCompanyId()
        );
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$',
                CompanyInterface::STATUS => CompanyInterface::STATUS_BLOCKED
            ],
            as: 'parent'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$',
                CompanyInterface::STATUS => CompanyInterface::STATUS_BLOCKED
            ],
            as: 'child'
        ),
    ]
    public function testCanAssignRelationToCompaniesWhoseStatusIsBlocked()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        /** @var CompanyInterface $childCompany */
        $childCompany = $this->fixtures->get('child');

        // create relation between parent and child
        $this->makeWebApiCall((int) $parentCompany->getId(), (int) $childCompany->getId());

        // assert relation is created
        $relationsToParentCompany = $this->getRelationsByParentId(
            (int) $parentCompany->getId()
        );

        $this->assertCount(1, $relationsToParentCompany);

        $relationToParentCompany = $relationsToParentCompany[0];

        $this->assertEquals(
            $parentCompany->getId(),
            $relationToParentCompany->getParentId()
        );

        $this->assertEquals(
            $parentCompany->getId(),
            $relationToParentCompany->getParentId()
        );

        $this->assertEquals(
            $childCompany->getId(),
            $relationToParentCompany->getCompanyId()
        );
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'parent2CompanySuperUser'),
        DataFixture(User::class, as: 'parent2CompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'child2CompanySuperUser'),
        DataFixture(User::class, as: 'child2CompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parent2CompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parent2CompanySuperUser.id$'
            ],
            as: 'parent2'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$'
            ],
            as: 'child'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$child2CompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$child2CompanySuperUser.id$'
            ],
            as: 'child2'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child.id$']
                ]
            ]
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent2.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child2.id$']
                ]
            ]
        )
    ]
    public function testCannotAssignParentCompanyAsAChildOfAnotherParentCompany()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        /** @var CompanyInterface $parent2Company */
        $parent2Company = $this->fixtures->get('parent2');

        $relationsToParentCompany = $this->getRelationsByParentId(
            (int) $parentCompany->getId()
        );
        $this->assertCount(1, $relationsToParentCompany);

        $relationsToParent2Company = $this->getRelationsByParentId(
            (int) $parent2Company->getId()
        );
        $this->assertCount(1, $relationsToParent2Company);

        // attempt to assign parent2 as a child of parent; exception should be thrown
        $this->makeWebApiCallAndExpectException(
            [
                'message' => (string) __('Cannot create some company relationships.'),
                'errors' => [
                    (string) __(
                        'Multi-tier assignments are not allowed. Company ID %company_id is a' .
                        ' parent company and cannot be assigned to another parent company.',
                        ['company_id' => $parent2Company->getId()]
                    ),
                ],
                'code' => WebapiException::HTTP_BAD_REQUEST
            ],
            (int) $parentCompany->getId(),
            (int) $parent2Company->getId()
        );

        // assert relation is not created
        $this->assertEmpty(
            $this->getRelationsByChildId((int) $parent2Company->getId())
        );
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'parent2CompanySuperUser'),
        DataFixture(User::class, as: 'parent2CompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'child2CompanySuperUser'),
        DataFixture(User::class, as: 'child2CompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parent2CompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parent2CompanySuperUser.id$'
            ],
            as: 'parent2'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$'
            ],
            as: 'child'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$child2CompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$child2CompanySuperUser.id$'
            ],
            as: 'child2'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child.id$']
                ]
            ]
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent2.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child2.id$']
                ]
            ]
        )
    ]
    public function testCannotAssignChildCompanyAsAChildOfAnotherChildCompany()
    {
        /** @var CompanyInterface $childCompany */
        $childCompany = $this->fixtures->get('child');

        /** @var CompanyInterface $child2Company */
        $child2Company = $this->fixtures->get('child2');

        $relationsToChildCompany = $this->getRelationsByChildId(
            (int) $childCompany->getId()
        );
        $this->assertCount(1, $relationsToChildCompany);

        $relationsToChild2Company = $this->getRelationsByChildId(
            (int) $child2Company->getId()
        );
        $this->assertCount(1, $relationsToChild2Company);

        // attempt to assign child2 as a child of child; exception should be thrown
        $this->makeWebApiCallAndExpectException(
            [
                'message' => (string) __(
                    'Multi-tier assignments are not allowed. Cannot complete the company assignment because' .
                    ' the specified parent company, %parent_id is already assigned to another parent company.',
                    ['parent_id' => $childCompany->getId()]
                ),
                'code' => WebapiException::HTTP_BAD_REQUEST
            ],
            (int) $childCompany->getId(),
            (int) $child2Company->getId()
        );

        // assert relation is not created
        $this->assertEmpty(
            $this->getRelationsByParentId((int) $childCompany->getId())
        );
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'child2CompanySuperUser'),
        DataFixture(User::class, as: 'child2CompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$'
            ],
            as: 'child'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$child2CompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$child2CompanySuperUser.id$'
            ],
            as: 'child2'
        ),
    ]
    public function testCanAssignMultipleChildCompaniesInOneRequest()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        /** @var CompanyInterface $childCompany */
        $childCompany = $this->fixtures->get('child');

        /** @var CompanyInterface $child2Company */
        $child2Company = $this->fixtures->get('child2');

        // create relation between parent and child, parent and child2 in one request
        $this->makeWebApiCall(
            (int) $parentCompany->getId(),
            (int) $childCompany->getId(),
            (int) $child2Company->getId()
        );

        $relationsToParentCompany = $this->getRelationsByParentId(
            (int) $parentCompany->getId()
        );

        $this->assertCount(2, $relationsToParentCompany);

        $this->assertEqualsCanonicalizing(
            [
                $childCompany->getId(),
                $child2Company->getId()
            ],
            array_map(function (RelationInterface $relationToParentCompany) {
                return $relationToParentCompany->getCompanyId();
            }, $relationsToParentCompany)
        );
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'child2CompanySuperUser'),
        DataFixture(User::class, as: 'child2CompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$'
            ],
            as: 'child'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$child2CompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$child2CompanySuperUser.id$'
            ],
            as: 'child2'
        ),
    ]
    public function testCanAssignMultipleChildCompaniesEvenIfSomeCannotBeSuccessfullyAssigned()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        /** @var CompanyInterface $childCompany */
        $childCompany = $this->fixtures->get('child');

        /** @var CompanyInterface $child2Company */
        $child2Company = $this->fixtures->get('child2');

        $invalidChild1Id = -1;
        $invalidChild2Id = -2;

        // create relation between parent and child, parent and child2, parent and invalidChildId in one request
        $this->makeWebApiCallAndExpectException(
            [
                'message' => (string) __('Cannot create some company relationships.'),
                'errors' => [
                    (string) __(
                        'No such entity with company_id = %company_id.',
                        ['company_id' => $invalidChild1Id]
                    ),
                    (string) __(
                        'No such entity with company_id = %company_id.',
                        ['company_id' => $invalidChild2Id]
                    )
                ],
                'code' => WebapiException::HTTP_BAD_REQUEST
            ],
            (int) $parentCompany->getId(),
            (int) $childCompany->getId(),
            (int) $child2Company->getId(),
            $invalidChild1Id,
            $invalidChild2Id
        );

        $relationsToParentCompany = $this->getRelationsByParentId(
            (int) $parentCompany->getId()
        );

        $this->assertCount(2, $relationsToParentCompany);

        $this->assertEqualsCanonicalizing(
            [
                $childCompany->getId(),
                $child2Company->getId()
            ],
            array_map(function (RelationInterface $relationToParentCompany) {
                return $relationToParentCompany->getCompanyId();
            }, $relationsToParentCompany)
        );
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parent'
        ),
    ]
    public function testParentIdAndChildIdCannotBeTheSame()
    {
        /** @var CompanyInterface $parentCompany */
        $parentCompany = $this->fixtures->get('parent');

        $this->makeWebApiCallAndExpectException(
            [
                'message' => (string) __('Cannot create some company relationships.'),
                'errors' => [
                    (string) __(
                        'Company ID %company_id cannot be assigned to itself.',
                        ['company_id' => $parentCompany->getId()]
                    )
                ],
                'code' => WebapiException::HTTP_BAD_REQUEST
            ],
            (int) $parentCompany->getId(),
            (int) $parentCompany->getId()
        );

        // assert relation is not created
        $this->assertEmpty(
            $this->getRelationsByChildId((int) $parentCompany->getId())
        );
    }

    /**
     * @param int $childId
     * @return RelationInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getRelationsByChildId(int $childId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('company_id', $childId)
            ->create();

        return $this->relationManager->getList($searchCriteria)->getItems();
    }

    /**
     * @param int $parentId
     * @return RelationInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRelationsByParentId(int $parentId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('parent_id', $parentId)
            ->create();

        return $this->relationManager->getList($searchCriteria)->getItems();
    }

    /**
     * @param int $parentCompanyId
     * @param int[] $childCompanyIds
     * @return void
     */
    private function makeWebApiCall(int $parentCompanyId, int ...$childCompanyIds): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/company/$parentCompanyId/relations",
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];

        $relationsPayload = [
            'relations' => array_map(
                function (int $companyId) {
                    return [
                        'company_id' => $companyId,
                    ];
                },
                $childCompanyIds
            )
        ];

        $response = $this->_webApiCall(
            $serviceInfo,
            $relationsPayload
        );

        $this->assertIsArray($response);
    }

    /**
     * @param array $expectedExceptionMessage
     * @param int $parentCompanyId
     * @param int[] $childCompanyIds
     * @return void
     */
    private function makeWebApiCallAndExpectException(
        array $expectedException,
        int $parentCompanyId,
        int ...$childCompanyIds
    ) {
        try {
            $this->makeWebApiCall($parentCompanyId, ...$childCompanyIds);
            $this->fail('Expected exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertEquals(
                $expectedException['code'],
                $e->getCode()
            );
            $exceptionMessage = json_decode($e->getMessage(), true);
            $this->assertEquals(
                $expectedException['message'],
                __($exceptionMessage['message'], $exceptionMessage['parameters'] ?? '')
            );
            $exceptionErrorMessages = $exceptionMessage['errors'] ?? [];
            $expectedExceptionErrors = $expectedException['errors'] ?? [];
            $this->assertCount(
                count($expectedExceptionErrors),
                $exceptionErrorMessages
            );
            foreach ($exceptionErrorMessages as $key => $exceptionErrorMessage) {
                $this->assertEquals(
                    $expectedExceptionErrors[$key],
                    __($exceptionErrorMessage['message'], $exceptionErrorMessage['parameters'] ?? '')
                );
            }
        }
    }
}
