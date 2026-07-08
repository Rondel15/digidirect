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

namespace Magento\Company;

use Magento\Company\Model\Company\Structure;
use Magento\Company\Model\CompanyContextInterface;
use Magento\Company\Model\Customer\Company;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for creation of a company
 */
class CreateCompanyTest extends TestCase
{
    /**
     * Verify the creation of a company with zero company context (by customer not assigned to a company)
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     */
    public function testStructure()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(Context::class)->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, 0, 0);
        $superUser = Bootstrap::getObjectManager()->get(AccountManagementInterface::class)->createAccount(
            $objectManager->get(CustomerInterfaceFactory::class)->create(
                [
                    'data' => [
                        'firstname' => 'Super',
                        'lastname' => 'User',
                        'email' => 'superuser' . rand() . '@example.com',
                        'group_id' => '1',
                        'website_id' => '1',
                        'store_id' => '1'
                    ]
                ]
            )
        );
        Bootstrap::getObjectManager()->get(Company::class)->createCompany(
            $superUser,
            [
                'company_name' => 'Company',
                'company_email' => 'company' . rand() . '@example.com',
                'street' => ['line1', 'line2'],
                'city' => 'Dublin',
                'country_id' => 'IE',
                'region' => '',
                'postcode' => 'D24 DCW0',
                'telephone' => '0857282681'
            ]
        );

        $this->assertEmpty(
            $objectManager->get(Structure::class)->getTreeByCustomerId($superUser->getId())->getChildren()
        );
    }
}
