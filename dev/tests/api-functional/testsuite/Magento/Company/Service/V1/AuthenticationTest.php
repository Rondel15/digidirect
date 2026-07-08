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

namespace Magento\Company\Service\V1;

use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test to verify, token creation is restricted for inactive customers.
 */
class AuthenticationTest extends WebapiAbstract
{
    #[
        Config('btob/website_configuration/company_active', 0),
        DataFixture(Customer::class, as: 'customer')
    ]
    public function testGenerateCustomerTokenWithInactiveCustomer()
    {
        $this->_markTestAsRestOnly();
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $customerRepo = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        $customerObj = $customerRepo->get($customer->getEmail());

        $companyAttributes = Bootstrap::getObjectManager()->get(CompanyCustomerInterfaceFactory::class)->create();
        $companyAttributes->setCompanyId(0);
        $companyAttributes->setStatus(0);

        $customerObj->getExtensionAttributes()->setCompanyAttributes($companyAttributes);
        $customerRepo->save($customerObj);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            '{"message":"The account sign-in was incorrect or your account is disabled temporarily. Please wait and '.
            'try again later."}'
        );
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/integration/customer/token',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = ['username' => $customer->getEmail(), 'password' => 'password'];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(
            Company::class,
            [
                'status' => CompanyInterface::STATUS_REJECTED,
                'super_user_id' => '$customer.id$',
                'reject_reason' => 'rejected',
                'rejected_at' => '2000-01-01 10:10:10'
            ],
            'company'
        )
    ]
    public function testGenerateCustomerTokenWithRejectedCompany()
    {
        $this->_markTestAsRestOnly();
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            '{"message":"The account sign-in was incorrect or your account is disabled temporarily. Please wait and '.
            'try again later."}'
        );
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/integration/customer/token',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = ['username' => $customer->getEmail(), 'password' => 'password'];
        $this->_webApiCall($serviceInfo, $requestData);
    }
}
