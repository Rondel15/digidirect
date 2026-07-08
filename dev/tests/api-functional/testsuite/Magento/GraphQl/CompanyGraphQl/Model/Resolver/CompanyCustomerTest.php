<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CompanyGraphQl\Model\Resolver;

use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\User\Test\Fixture\User;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\AssignCustomer;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;

class CompanyCustomerTest extends GraphQlAbstract
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer
     */
    private $companyCustomerResource;

    /**
     * @var CompanyCustomerInterfaceFactory
     */
    private $companyExtensionAttributesFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->fixtureManager = $objectManager->get(FixtureManager::class);
        $this->companyCustomerResource = $objectManager->get(\Magento\Company\Model\ResourceModel\Customer::class);
        $this->companyExtensionAttributesFactory = $objectManager->get(CompanyCustomerInterfaceFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Company/_files/company.php
     */
    public function testResolverCacheIsProperlyHydratedForCompanyAdmin()
    {
        $companyAdmin = $this->customerRepository->get('admin@magento.com');

        $token = $this->generateCustomerToken($companyAdmin->getEmail(), 'password');
        $query = $this->getCustomerQuery();

        $responseBeforeCaching = $this->graphQlQuery(
            $query,
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $token,
            ]
        );

        // call again to ensure it produces the same response without any errors
        $responseAfterCaching = $this->graphQlQuery(
            $query,
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $token,
            ]
        );

        $this->assertEquals(
            $responseBeforeCaching,
            $responseAfterCaching
        );
    }

    #[
        AppIsolation(true),
        Config('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'superUser_a'),
        DataFixture(Customer::class, as: 'superUser_b'),
        DataFixture(Customer::class, as: 'customer_ab'),
        DataFixture(User::class, as: 'admin_user'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin_user.id$',
                CompanyInterface::SUPER_USER_ID => '$superUser_a.id$',
                CompanyInterface::NAME => 'Magento_A'
            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin_user.id$',
                CompanyInterface::SUPER_USER_ID => '$superUser_b.id$',
                CompanyInterface::NAME => 'Magento_B'
            ],
            'company_b'
        ),
        DataFixture(
            AssignCustomer::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer_ab.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_b.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer_ab.id$',
                CompanyCustomerInterface::IS_DEFAULT => 0
            ]
        )
    ]
    public function testCustomerResolverCacheForCompanyContext()
    {
        /** @var CustomerInterface $companyUser */
        $companyUser = $this->customerRepository->get(
            $this->fixtureManager->getStorage()->get('customer_ab')->getEmail()
        );
        /** @var CompanyInterface $companyA */
        $companyA = $this->fixtureManager->getStorage()->get('company_a');
        /** @var CompanyInterface $companyB */
        $companyB = $this->fixtureManager->getStorage()->get('company_b');

        $this->updateJobTitleInCompany((int)$companyUser->getId(), (int)$companyA->getId(), 'Company A user');
        $this->updateJobTitleInCompany((int)$companyUser->getId(), (int)$companyB->getId(), 'Company B user');

        $token = $this->generateCustomerToken($companyUser->getEmail(), 'password');
        $query = $this->getCustomerQuery();

        $response = $this->execQueryForCompany($query, $token, (int)$companyA->getId());
        $this->assertEquals('Company A user', $response['customer']['job_title']);
        $response = $this->execQueryForCompany($query, $token, (int)$companyB->getId());
        $this->assertEquals('Company B user', $response['customer']['job_title']);

        // make sure data is read from cache
        $this->updateJobTitleInCompany((int)$companyUser->getId(), (int)$companyA->getId(), 'Company A user updated');
        $response = $this->execQueryForCompany($query, $token, (int)$companyA->getId());

        // This regexp is added to preserve for backward compatibility with 2.4.6-x versions.
        $this->assertMatchesRegularExpression(
            '/^(Company A user)( updated)?$/',
            $response['customer']['job_title']
        );

        // invalidate cache by updating customer
        $this->customerRepository->save($companyUser);

        // ensure data is invalidated in cache
        $response = $this->execQueryForCompany($query, $token, (int)$companyA->getId());
        // must be null as original test runtime model extension attributes were not updated
        // and job title remained null
        $this->assertNull($response['customer']['job_title']);
    }

    /**
     * Execute query wrapper for better readability.
     *
     * @param string $query
     * @param string $token
     * @param int $companyId
     * @return array|bool|float|int|string
     * @throws \Exception
     */
    private function execQueryForCompany(string $query, string $token, int $companyId)
    {
        return $this->graphQlQuery(
            $query,
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $token,
                'X-Adobe-Company' => Bootstrap::getObjectManager()->get(Uid::class)->encode((string) $companyId),
            ]
        );
    }

    /**
     * Update job title in company by company and customer ids.
     *
     * @param int $customerId
     * @param int $companyId
     * @param string $jobTitle
     * @return array
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateJobTitleInCompany(int $customerId, int $companyId, string $jobTitle)
    {
        $companyAttributes = $this->companyCustomerResource->getCompanyAttributesById($customerId, $companyId);
        $companyExtensionAttributes = $this->companyExtensionAttributesFactory->create();
        $companyExtensionAttributes->setData($companyAttributes);
        $companyExtensionAttributes->setJobTitle($jobTitle);
        $this->companyCustomerResource->saveAdvancedCustomAttributes($companyExtensionAttributes);
        return $companyAttributes;
    }

    /**
     * Generate customer query.
     *
     * @return string
     */
    private function getCustomerQuery(): string
    {
        return <<<QUERY
{
    customer {
        firstname
        lastname
        email
        job_title
        role {
            name
            users_count
            permissions {
                children {
                    text
                }
                text
            }
        }
        team {
            name
        }
        telephone
        status
        structure_id
    }
}
QUERY;
    }

    /**
     * Get customer login token
     *
     * @param string $email
     * @param string $password
     * @return string
     */
    private function generateCustomerToken(string $email, string $password): string
    {
        $mutation = <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$email}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;

        $response = $this->graphQlMutation(
            $mutation
        );

        return $response['generateCustomerToken']['token'];
    }
}
