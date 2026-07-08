<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Company\Controller\Adminhtml\Index;

use Magento\Authorization\Test\Fixture\Role as RoleFixture;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Bootstrap;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\User\Test\Fixture\User as UserFixture;
use Magento\User\Model\UserFactory;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends AbstractBackendController
{
    /**
     * @var string
     */
    private static $companyName = 'New Company';

    /**
     * @var string
     */
    private static $companyEmail = 'company@test.com';

    /**
     * @var string
     */
    private static $customerEmail = 'company.admin@test.com';

    /**
     * @var string
     */
    protected $resource = Save::ADMIN_RESOURCE;

    /**
     * @var string
     */
    protected $uri = 'backend/company/index/save';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    /**
     * Test that a backoffice admin can create a new B2B company with enabled Website Restrictions
     *
     * Given a backoffice admin with General > Website Restrictions > Access Restriction set to Yes in default store
     * And General > Website Restrictions > Restriction Mode set to Private Sales: Login Only
     * When the admin requests to save a new company with valid form data
     * Then the company and company admin are successfully created
     *
     * @magentoConfigFixture default_store general/restriction/is_active 1
     * @magentoConfigFixture default_store general/restriction/mode 1
     */
    public function testCanCreateCompanyWithWebsiteRestrictions()
    {
        $params = $this->getRequestData();
        $request = $this->getRequest();
        $request->setParams($params);
        $request->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/company/index/save');

        $message = $this->getSuccessMessage();
        self::assertEquals('You have created company ' . self::$companyName . '.', $message);

        $customer = $this->getCustomer(self::$customerEmail);
        self::assertNotEmpty($customer);

        $company = $this->getCompany(self::$companyEmail);
        self::assertNotEmpty($company);

        $this->deleteCompany($company);
        $this->deleteCustomer($customer);
    }

    /**
     * Test that a backoffice admin can create a company using any country for a company address, regardless of whether
     * it's in any allowed countries list on any website
     *
     * Given a backoffice admin with General > Allow Countries set to France in default website
     * And General > Allow Countries set to Spain, United States, United Kingdom, and Germany in a second website
     * When the admin requests to save a new company in default website with valid form data using a French address
     * Then the company and company admin are successfully created
     * When the admin requests to save a new company in default website with valid form data using a Spanish address
     * Then the company and company admin are successfully created
     * When the admin requests to save a new company in default website with valid form data using a Mexican address
     * Then the company and company admin are successfully created
     *
     * @param string $countryId
     * @param int $regionId
     * @dataProvider countryDataProvider
     *
     * @magentoDataFixture Magento/Store/_files/websites_different_countries.php
     */
    public function testDontCareWhatCountryIsUsedForCompanyAddress(string $countryId, int $regionId): void
    {
        $params = $this->getRequestData();

        // override country/region id
        $params['address']['country_id'] = $countryId;
        $params['address']['region_id'] = $regionId;

        $request = $this->getRequest();
        $request->setParams($params);
        $request->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/company/index/save');

        $message = $this->getSuccessMessage();
        self::assertEquals('You have created company ' . self::$companyName . '.', $message);

        $company = $this->getCompany(self::$companyEmail);
        self::assertNotEmpty($company);
    }

    /**
     * Data provider for testDontCareWhatCountryIsUsedForCompanyAddress
     *
     * @return array[]
     */
    public static function countryDataProvider()
    {
        return [
            ['FR', 228],
            ['ES', 139],
            ['MX', 798],
        ];
    }

    /**
     * Gets request params.
     *
     * @return array
     */
    private function getRequestData(): array
    {
        return [
            'general' => [
                'company_name' => self::$companyName,
                'company_email' => self::$companyEmail,
                'sales_representative_id' => 1,
                'status' => 1,
            ],
            'address' => [
                'street' => ['6161 West Centinela Avenue'],
                'city' => 'Culver City',
                'postcode' => 90230,
                'country_id' => 'US',
                'region_id' => 12,
                'telephone' => '555-55-555-55'
            ],
            'company_admin' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => self::$customerEmail,
                'gender' => 3,
                'website_id' => 1,
            ],
            'settings' => [
                'customer_group_id' => 1
            ]
        ];
    }

    /**
     * Gets success message after dispatching the controller.
     *
     * @return string|null
     */
    private function getSuccessMessage(): ?string
    {
        /** @var ManagerInterface $messageManager */
        $messageManager = $this->_objectManager->get(ManagerInterface::class);
        $messages = $messageManager->getMessages(true)->getItems();
        if ($messages) {
            return $messages[0]->getText();
        }

        return null;
    }

    /**
     * Gets customer entity by email.
     *
     * @param string $email
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomer(string $email): CustomerInterface
    {
        /** @var CustomerRepositoryInterface $repository */
        $repository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        return $repository->get($email);
    }

    /**
     * Deletes customer entity.
     *
     * @param CustomerInterface $customer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function deleteCustomer(CustomerInterface $customer)
    {
        /** @var CustomerRepositoryInterface $repository */
        $repository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $repository->delete($customer);
    }

    /**
     * Gets company entity by email.
     *
     * @param string $email
     * @return CompanyInterface
     */
    private function getCompany(string $email): CompanyInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('company_email', $email)
            ->create();
        /** @var CompanyRepositoryInterface $repository */
        $repository = $this->_objectManager->get(CompanyRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Deletes company entity.
     *
     * @param CompanyInterface $company
     */
    private function deleteCompany(CompanyInterface $company)
    {
        /** @var CompanyRepositoryInterface $repository */
        $repository = $this->_objectManager->get(CompanyRepositoryInterface::class);
        $repository->delete($company);
    }

    /**
     * Test backoffice admin has access to /company/index/save when granted Magento_Company::manage permission
     *
     * Given a backoffice admin
     * When Magento_Company::manage ACL permission is allowed for the admin's role
     * And a POST request is made to save a new company or update an existing company
     * Then the HTTP response code is 200
     */
    public function testAdminCanCreateOrEditCompanyWhenCompanyManageACLEnabled()
    {
        return parent::testAclHasAccess();
    }

    /**
     * Test backoffice admin does not have access to /company/index/save when denied Magento_Company::manage permission
     *
     * Given a backoffice admin
     * When Magento_Company::manage ACL permission is denied for the admin's role
     * And a POST request is made to save a new company or update an existing company
     * Then the HTTP response code is 403
     */
    public function testAdminCannotCreateOrEditCompanyWhenCompanyManageACLDisabled()
    {
        return parent::testAclNoAccess();
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    #[
        DataFixture(
            RoleFixture::class,
            [
                'resources' => [
                    "Magento_Company::index",
                    "Magento_Company::manage",
                    "Magento_Company::view",
                    "Magento_Company::edit",
                    "Magento_Company::delete",
                    "Magento_Customer::view",
                ]
            ],
            'lower_level_admin_user'
        ),
    ]
    public function testAdminCannotCreateNewCompanyWhenCompanySaveAclDisabled()
    {
        $userFactory = $this->_objectManager->get(UserFactory::class);
        $user = $userFactory->create()->loadByUsername(Bootstrap::ADMIN_NAME);
        $role = FixtureManager::getStorage()->get('lower_level_admin_user');
        $user->setRoleId($role->getId());
        $user->save();
        $params = $this->getRequestData();
        $request = $this->getRequest();
        $request->setParams($params);
        $request->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/company/index/save');
        $this->assertSame($this->expectedNoAccessResponseCode, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    #[
        DataFixture(
            RoleFixture::class,
            [
                'resources' => [
                    "Magento_Company::index",
                    "Magento_Company::manage",
                    "Magento_Company::add",
                    "Magento_Company::view",
                    "Magento_Company::edit",
                    "Magento_Company::delete",
                    "Magento_Customer::view"
                ]
            ],
            'lower_level_admin_user'
        ),
    ]
    public function testAdminCanCreateNewCompanyWhenCompanyAddAclEnabled()
    {
        $userFactory = $this->_objectManager->get(UserFactory::class);
        $user = $userFactory->create()->loadByUsername(Bootstrap::ADMIN_NAME);
        $role = FixtureManager::getStorage()->get('lower_level_admin_user');
        $user->setRoleId($role->getId());
        $user->save();
        $params = $this->getRequestData();
        $params['general']['sales_representative_id'] = $user->getId();
        $params['back'] = 1;
        $request = $this->getRequest();
        $request->setParams($params);
        $request->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/company/index/save');
        $this->assertRedirect();
        $responseUri = $this->getResponse()->getHeaders()->get('location')->getUri();
        $this->assertStringContainsString('company/index/edit/id/', $responseUri);
        $matches = [];
        $companyId = 0;
        if (preg_match('/\/id\/(\d+)\//', $responseUri, $matches)) {
            $companyId = $matches[1];

        } else {
            $this->fail('company id not found in response');
        }
        $companyByEmail = $this->getCompany($params['general']['company_email']);
        $this->assertEquals($companyId, $companyByEmail->getId());
    }

    /**
     * Test create company with custom file attribute
     *
     * @magentoDataFixture Magento/CustomerCustomAttributes/_files/customer_custom_file_attribute.php
     */
    public function testCreateCompanyWithCustomFileCustomerAttribute()
    {
        $fileSystem = $this->_objectManager->get(Filesystem::class);
        $mediaDirectory = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->delete('customer');
        $mediaDirectory->create($mediaDirectory->getRelativePath('customer/tmp/'));
        $fixtureFile = realpath(INTEGRATION_TESTS_DIR . '/testsuite/Magento/Customer/_files/image/magento.jpg');

        $tmpFilePath = $mediaDirectory->getAbsolutePath('customer/tmp/magento.jpg');
        $mediaDirectory->getDriver()->filePutContents(
            $tmpFilePath,
            file_get_contents($fixtureFile)
        );

        $fileData = [
            'name' => 'magento.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $tmpFilePath,
            'error' => 0,
            'size' => 139416,
        ];

        foreach ($fileData as $field => $value) {
            $_FILES['company_admin'][$field]['test_file_attribute'] = $value;
        }

        $params = $this->getRequestData();
        $params['company_admin']['test_file_attribute'] = $fileData;
        $request = $this->getRequest();
        $request->setParams($params);
        $request->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/company/index/save');

        $message = $this->getSuccessMessage();
        self::assertEquals('You have created company ' . self::$companyName . '.', $message);

        $customer = $this->getCustomer(self::$customerEmail);
        self::assertNotEmpty($customer);

        $company = $this->getCompany(self::$companyEmail);
        self::assertNotEmpty($company);

        $this->deleteCompany($company);
        $this->deleteCustomer($customer);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(CustomerFixture::class, as: 'admin'),
        DataFixture(UserFixture::class, as: 'user'),
        DataFixture(
            CompanyFixture::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin.id$',
            ],
            'company'
        ),
    ]
    /**
     * @return void
     */
    public function testCompanySaveWithNewNonExistingAdmin()
    {
        /** @var CompanyInterface $company */
        $company = FixtureManager::getStorage()->get('company');

        $adminEmail = FixtureManager::getStorage()->get('admin')->getEmail();
        $admin = $this->getCustomer($adminEmail);

        $requestData = $this->prepareCompanyRequestData($company, $admin);

        $nonExistentEmail = 'nonexistent' . $adminEmail;
        $requestData['company_admin']['email'] = $nonExistentEmail;

        $request = $this->getRequest();
        $request->setParams($requestData);
        $request->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch($this->uri . '/back/edit');

        $this->assertRedirect($this->stringContains('company/index/edit/id/' . $company->getId()));
        self::assertEquals('You have saved company ' . $company->getCompanyName() . '.', $this->getSuccessMessage());

        $updatedCompany = $this->getCompany($company->getCompanyEmail());
        self::assertNotEmpty($company);
        self::assertEquals($company->getId(), $updatedCompany->getId());
        self::assertNotEquals($admin->getId(), $updatedCompany->getSuperUserId());

        $nonExistentAdmin = $this->getCustomer($nonExistentEmail);
        self::assertEquals($updatedCompany->getSuperUserId(), $nonExistentAdmin->getId());
        self::assertEquals($nonExistentEmail, $nonExistentAdmin->getEmail());
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(CustomerFixture::class, as: 'admin'),
        DataFixture(CustomerFixture::class, as: 'admin_new'),
        DataFixture(UserFixture::class, as: 'user'),
        DataFixture(
            CompanyFixture::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin.id$',
            ],
            'company'
        ),
    ]
    /**
     * @return void
     */
    public function testCompanySaveWithNewExistingAdmin()
    {
        /** @var CompanyInterface $company */
        $company = FixtureManager::getStorage()->get('company');

        $adminEmail = FixtureManager::getStorage()->get('admin')->getEmail();
        $admin = $this->getCustomer($adminEmail);

        $newAdminEmail = FixtureManager::getStorage()->get('admin_new')->getEmail();
        $newAdmin = $this->getCustomer($newAdminEmail);

        $requestData = $this->prepareCompanyRequestData($company, $admin);

        $requestData['company_admin']['email'] = $newAdminEmail;

        $request = $this->getRequest();
        $request->setParams($requestData);
        $request->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch($this->uri . '/back/edit');

        $this->assertRedirect($this->stringContains('company/index/edit/id/' . $company->getId()));
        self::assertEquals('You have saved company ' . $company->getCompanyName() . '.', $this->getSuccessMessage());

        $updatedCompany = $this->getCompany($company->getCompanyEmail());
        self::assertNotEmpty($company);
        self::assertEquals($company->getId(), $updatedCompany->getId());
        self::assertNotEquals($admin->getId(), $updatedCompany->getSuperUserId());

        self::assertEquals($updatedCompany->getSuperUserId(), $newAdmin->getId());
        self::assertEquals($newAdminEmail, $newAdmin->getEmail());
    }

    /**
     * Converts Company object to request data.
     *
     * @param CompanyInterface $company
     * @param CustomerInterface $admin
     * @return array
     */
    private function prepareCompanyRequestData(CompanyInterface $company, CustomerInterface $admin): array
    {
        return [
            'id' => $company->getId(),
            'general' => [
                'company_name' => $company->getCompanyName(),
                'company_email' => $company->getCompanyEmail(),
                'sales_representative_id' => $company->getSalesRepresentativeId(),
                'status' => $company->getStatus(),
                'reject_reason' => $company->getRejectReason(),
            ],
            'information' => [
                'legal_name' => $company->getLegalName(),
                'vat_tax_id' => $company->getVatTaxId(),
                'reseller_id' => $company->getResellerId(),
                'comment' => $company->getComment(),
            ],
            'address' => [
                'street' => $company->getStreet(),
                'city' => $company->getCity(),
                'postcode' => $company->getPostcode(),
                'country_id' => $company->getCountryId(),
                'region' => $company->getRegion(),
                'region_id' => $company->getRegionId(),
                'telephone' => $company->getTelephone(),
            ],
            'company_admin' => [
                'job_title' => $admin->getExtensionAttributes()->getCompanyAttributes()->getJobTitle(),
                'prefix' => $admin->getPrefix(),
                'firstname' => $admin->getFirstname(),
                'middlename' => $admin->getMiddlename(),
                'lastname' => $admin->getLastname(),
                'suffix' => $admin->getSuffix(),
                'email' => $admin->getEmail(),
                'sendemail_store_id' => $admin->getStoreId(),
                'gender' => $admin->getGender(),
                'website_id' => $admin->getWebsiteId(),
            ],
            'settings' => [
                'customer_group_id' => $admin->getGroupId(),
            ]
        ];
    }
}
