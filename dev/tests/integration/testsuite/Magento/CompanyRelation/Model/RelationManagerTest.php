<?php
/**
 * ADOBE CONFIDENTIAL
 *
 * Copyright 2023 Adobe
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

namespace Magento\CompanyRelation\Model;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\CompanyRelation\Api\Data\RelationInterface;
use Magento\CompanyRelation\Model\Relation\RepositoryInterface;
use Magento\CompanyRelation\Test\Fixture\AssignRelations;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;

class RelationManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var RepositoryInterface|MockObject
     */
    private $relationRepositoryMock;

    /**
     * @var \Magento\TestFramework\Fixture\DataFixtureStorage
     */
    private $fixtureStorage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtureStorage = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->relationRepositoryMock = $this->createMock(RepositoryInterface::class);
    }

    #[
        AppArea('adminhtml'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'company_a_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'company_b_admin'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'sales_rep_user'),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Company A',
                CompanyInterface::SUPER_USER_ID => '$company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'company_a'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Company B',
                CompanyInterface::SUPER_USER_ID => '$company_b_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'company_b'
        ),
    ]
    /**
     * @dataProvider createDataProvider
     */
    public function testCreateCouldNotSaveException($area, $expectedMessage)
    {
        $application = Bootstrap::getInstance()->getBootstrap()->getApplication();
        $application->reinitialize();
        $application->loadArea($area);

        $companyAId = $this->fixtureStorage->get('company_a')->getData(CompanyInterface::COMPANY_ID);
        $companyBId = $this->fixtureStorage->get('company_b')->getData(CompanyInterface::COMPANY_ID);
        $relation = $this->objectManager->create(\Magento\CompanyRelation\Api\Data\RelationInterface::class);
        $relation->setParentId((int) $companyAId);
        $relation->setCompanyId((int) $companyBId);

        $this->relationRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new CouldNotSaveException(__('Test create exception message')));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessageMatches($expectedMessage);

        /** @var RelationManager $relationManager */
        $relationManager = $this->objectManager->create(RelationManager::class, [
            'relationRepository' => $this->relationRepositoryMock
        ]);
        $relationManager->create((int) $companyAId, [$relation]);
    }

    public static function createDataProvider()
    {
        return [
            [
                \Magento\Framework\App\Area::AREA_ADMINHTML,
                '$Cannot assign the <b>[\w\s]+<\/b> company to the selected parent company, <b>[\w\s]+<\/b>\.$'],
            [
                \Magento\Framework\App\Area::AREA_WEBAPI_REST,
                '$Cannot create the company relationship between parent ID \d+ and company ID \d+\.$'],
        ];
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'company_a_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'company_b_admin'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'sales_rep_user'),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Company A',
                CompanyInterface::SUPER_USER_ID => '$company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'company_a'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Company B',
                CompanyInterface::SUPER_USER_ID => '$company_b_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'company_b'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID => '$company_a.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$company_b.id$'],
                ],
            ],
        ),
    ]
    /**
     * @dataProvider deleteDataProvider
     */
    public function testDeleteCouldNotDeleteException($area, $expectedMessage)
    {
        $application = Bootstrap::getInstance()->getBootstrap()->getApplication();
        $application->reinitialize();
        $application->loadArea($area);

        $companyAId = $this->fixtureStorage->get('company_a')->getData(CompanyInterface::COMPANY_ID);
        $companyBId = $this->fixtureStorage->get('company_b')->getData(CompanyInterface::COMPANY_ID);

        $this->relationRepositoryMock
            ->expects($this->once())
            ->method('delete')
            ->willThrowException(new CouldNotDeleteException(__('Test delete exception message')));

        $this->expectException(CouldNotDeleteException::class);
        $this->expectExceptionMessageMatches($expectedMessage);

        /** @var RelationManager $relationManager */
        $relationManager = $this->objectManager->create(RelationManager::class, [
            'relationRepository' => $this->relationRepositoryMock
        ]);
        $relationManager->delete((int) $companyAId, (int) $companyBId);
    }

    public static function deleteDataProvider()
    {
        return [
            [
                \Magento\Framework\App\Area::AREA_ADMINHTML,
                '$Cannot unassign the <b>[\w\s]+<\/b> company from parent company <b>[\w\s]+<\/b>\.$'
            ],
            [
                \Magento\Framework\App\Area::AREA_WEBAPI_REST,
                '$Cannot delete the company relationship for parent ID \d+ and company ID \d+\.$',
            ]
        ];
    }
}
