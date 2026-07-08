<?php
/**
 * ADOBE CONFIDENTIAL
 * Copyright 2023 Adobe
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

namespace Magento\CompanyPayment\Model\Company;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\CompanyPayment\Model\Source\CompanyApplicablePaymentMethod;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\User\Test\Fixture\User as AdminUserFixture;

class CompanyRepositoryTest extends AbstractBackendController
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->companyRepository = $this->objectManager->get(CompanyRepositoryInterface::class);

        parent::setUp();
    }

    #[
        DataFixture(CustomerFixture::class, as: 'companyAdmin'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser'),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Company',
                CompanyInterface::SUPER_USER_ID => '$companyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'company'
        ),
    ]
    public function testCanSaveApplicablePaymentMethods()
    {
        /** @var CompanyInterface $company */
        $company = $this->fixtures->get('company');

        // set company's applicable payment method to all payment methods
        $company->getExtensionAttributes()->setApplicablePaymentMethod(
            CompanyApplicablePaymentMethod::ALL_PAYMENT_METHODS_VALUE
        );

        $this->companyRepository->save($company);

        // assert company's applicable payment method is set to all payment methods
        $this->assertSame(
            (string) CompanyApplicablePaymentMethod::ALL_PAYMENT_METHODS_VALUE,
            $company->getExtensionAttributes()->getApplicablePaymentMethod()
        );

        // change company's applicable payment method to B2B payment methods via admin save controller
        $company->getExtensionAttributes()->setApplicablePaymentMethod(
            CompanyApplicablePaymentMethod::B2B_PAYMENT_METHODS_VALUE
        );

        $this->companyRepository->save($company);

        // assert company's applicable payment method is set to B2B payment methods
        $this->assertSame(
            (string) CompanyApplicablePaymentMethod::B2B_PAYMENT_METHODS_VALUE,
            $company->getExtensionAttributes()->getApplicablePaymentMethod()
        );
    }
}
