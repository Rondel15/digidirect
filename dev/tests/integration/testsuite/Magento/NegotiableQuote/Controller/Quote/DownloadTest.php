<?php
/************************************************************************
 * Copyright 2019 Adobe
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
 ***********************************************************************/
declare(strict_types=1);

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Company\Api\AclInterface;
use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\NegotiableQuote\Api\CommentLocatorInterface;
use Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Model\Attachment\DownloadPermission\AllowCustomer;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\User\Test\Fixture\User;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Company\Test\Fixture\Role;
use Magento\Company\Model\Role as RoleModel;
use Magento\Company\Model\Company\Structure as StructureManager;
use Magento\Company\Model\ResourceModel\Structure\Tree as StructureTree;

/**
 * @magentoAppArea frontend
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadTest extends AbstractController
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AclInterface
     */
    private $aclRoleManager;

    /**
     * @var CompanyCustomerInterfaceFactory
     */
    private $companyAttributesFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->searchCriteriaBuilder = $this->_objectManager->create(SearchCriteriaBuilder::class);
        $this->quoteRepository = $this->_objectManager->create(NegotiableQuoteRepositoryInterface::class);
        $this->customerSession = $this->_objectManager->get(CustomerSession::class);
        $this->customerRegistry = $this->_objectManager->get(CustomerRegistry::class);
        $this->customerRepository = $this->_objectManager->create(CustomerRepositoryInterface::class);
        $this->companyAttributesFactory = $this->_objectManager->get(CompanyCustomerInterfaceFactory::class);
        $this->aclRoleManager = $this->_objectManager->get(AclInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->setCustomerId(null);
        $this->customerRegistry->removeByEmail('customer@example.com');
        $this->customerRegistry->removeByEmail('quote_customer_email@example.com');

        parent::tearDown();
    }

    #[
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_WEBSITE)
    ]
    /**
     * @return void
     */
    public function testDownloadAttachmentByNotLoggedInCustomer(): void
    {
        $this->dispatch('negotiable_quote/quote/download/attachmentId/1/');

        $this->assertRedirect($this->stringContains('customer/account/login'));
        $this->assertSessionMessages(
            $this->equalTo([$this->getMessageText('Please sign in to download.')]),
            MessageInterface::TYPE_NOTICE
        );
    }

    #[
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_WEBSITE),
    ]
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testDownloadMissingAttachmentByLoggedInCustomer(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->customerSession->loginById($customer->getId());

        $this->dispatch('negotiable_quote/quote/download/attachmentId/100500');

        $this->assertRedirect($this->stringContains('negotiable_quote/quote'));
        $this->assertSessionMessages(
            $this->equalTo([$this->getMessageText('We can\'t find the file you requested.')]),
            MessageInterface::TYPE_NOTICE
        );
    }

    #[
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_WEBSITE)
    ]
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/NegotiableQuote/_files/negotiable_quote_with_attachment.php
     * @return void
     */
    public function testDownloadAttachmentByNotAuthorizedCustomer(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->customerSession->loginById($customer->getId());

        $attachment = $this->getQuoteAttachment('quote_with_comment_attachment');
        if ($attachment === false) {
            $this->fail('Could not load attachment by negotiable quote name');
        }

        $this->dispatch('negotiable_quote/quote/download/attachmentId/' . $attachment->getAttachmentId());

        $this->assertRedirect($this->stringContains('negotiable_quote/quote'));
        $this->assertSessionMessages(
            $this->equalTo([$this->getMessageText('We can\'t find the file you requested.')]),
            MessageInterface::TYPE_NOTICE
        );
    }

    #[
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_WEBSITE)
    ]
    /**
     * @magentoDataFixture Magento/NegotiableQuote/_files/negotiable_quote_with_attachment.php
     * @return void
     */
    public function testDownloadAttachmentByLoggedInCustomerNoNQSelfPermission(): void
    {
        $customer = $this->customerRepository->get('quote_customer_email@example.com');
        $this->customerSession->loginById($customer->getId());

        $attachment = $this->getQuoteAttachment('quote_with_comment_attachment');
        if ($attachment === false) {
            $this->fail('Could not load attachment by negotiable quote name');
        }

        ob_start();
        $this->dispatch('negotiable_quote/quote/download/attachmentId/' . $attachment->getAttachmentId());
        ob_end_clean();

        $this->assertRedirect($this->stringContains('negotiable_quote/quote'));
        $this->assertSessionMessages(
            $this->equalTo([$this->getMessageText('We can\'t find the file you requested.')]),
            MessageInterface::TYPE_NOTICE
        );
    }

    #[
        Config('btob/website_configuration/company_active', 1, ScopeInterface::SCOPE_WEBSITE),
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_WEBSITE),
        DataFixture(Customer::class, as: 'admin_customer'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            CompanyFixture::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin_customer.id$'
            ],
            'company'
        ),
        DataFixture(Customer::class, as: 'companyManager'),
        DataFixture(
            Role::class,
            [
                'company_id' => '$company.id$',
                'permissions' => [
                    [
                        'resource_id' => 'Magento_NegotiableQuote::all',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::view_quotes',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::manage_quotes_sub',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::view_quotes_sub',
                        'permission' => 'deny'
                    ],
                ]
            ],
            'company_manager_role'
        ),
    ]
    /**
     * @magentoDataFixture Magento/NegotiableQuote/_files/negotiable_quote_with_attachment.php
     * @return void
     */
    public function testCompanyManagerDownloadNoNQSubPermission(): void
    {
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $managerRole = DataFixtureStorageManager::getStorage()->get('company_manager_role');
        $companyManager = DataFixtureStorageManager::getStorage()->get('companyManager');
        $companyManager = $this->customerRepository->getById($companyManager->getId());
        $this->assignCustomerToCompany($companyManager, $company);
        $this->assignRoleToCustomer($companyManager, $managerRole);
        $companySubCustomer = $this->customerRepository->get('quote_customer_email@example.com');
        $this->assignCustomerToCompany($companySubCustomer, $company);
        $this->moveCompanyUser($companyManager, $companySubCustomer);
        $this->customerSession->loginById($companyManager->getId());

        $attachment = $this->getQuoteAttachment('quote_with_comment_attachment');
        if ($attachment === false) {
            $this->fail('Could not load attachment by negotiable quote name');
        }

        $this->dispatch('negotiable_quote/quote/download/attachmentId/' . $attachment->getAttachmentId());

        $this->assertRedirect($this->stringContains('negotiable_quote/quote/'));
    }

    #[
        Config('btob/website_configuration/company_active', 1, ScopeInterface::SCOPE_WEBSITE),
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_WEBSITE),
        DataFixture(Customer::class, as: 'admin_customer'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            CompanyFixture::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin_customer.id$'
            ],
            'company'
        ),
        DataFixture(Customer::class, as: 'companyManager'),
        DataFixture(
            Role::class,
            [
                'company_id' => '$company.id$',
                'permissions' => [
                    [
                        'resource_id' => 'Magento_NegotiableQuote::all',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::view_quotes',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::manage_quotes_sub',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::view_quotes_sub',
                        'permission' => 'allow'
                    ],
                ]
            ],
            'company_manager_role'
        ),
    ]
    /**
     * @magentoDataFixture Magento/NegotiableQuote/_files/negotiable_quote_with_attachment.php
     * @return void
     */
    public function testCompanyManagerDownloadWithNQSubPermission(): void
    {
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $managerRole = DataFixtureStorageManager::getStorage()->get('company_manager_role');
        $companyManager = DataFixtureStorageManager::getStorage()->get('companyManager');
        $companyManager = $this->customerRepository->getById($companyManager->getId());
        $this->assignCustomerToCompany($companyManager, $company);
        $this->assignRoleToCustomer($companyManager, $managerRole);
        $companySubCustomer = $this->customerRepository->get('quote_customer_email@example.com');
        $this->assignCustomerToCompany($companySubCustomer, $company);
        $this->moveCompanyUser($companyManager, $companySubCustomer);
        $this->customerSession->loginById($companyManager->getId());
        $mock = $this->createMock(AllowCustomer::class);
        $mock->method('isAllowed')->willReturn(true);
        $this->_objectManager->addSharedInstance($mock, AllowCustomer::class);

        $attachment = $this->getQuoteAttachment('quote_with_comment_attachment');
        if ($attachment === false) {
            $this->fail('Could not load attachment by negotiable quote name');
        }

        ob_start();
        $this->dispatch('negotiable_quote/quote/download/attachmentId/' . $attachment->getAttachmentId());
        ob_end_clean();

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $this->assertHeaderPcre('Pragma', '/^public$/');
        $this->assertHeaderPcre('Content-Type', "/^application\/octet-stream$/");
        $this->assertHeaderPcre('Content-Disposition', '/^attachment; filename="' . $attachment->getFileName() . '"$/');
    }

    /**
     * Returns attachment by quote name.
     *
     * @param string $quoteName
     * @return CommentAttachmentInterface|bool
     */
    private function getQuoteAttachment(string $quoteName)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(NegotiableQuoteInterface::QUOTE_NAME, $quoteName)
            ->create();
        /** @var NegotiableQuoteInterface[] $quotes */
        $quotes = $this->quoteRepository->getList($searchCriteria)->getItems();
        $quote = reset($quotes);
        if ($quote === false) {
            return false;
        }

        /** @var CommentLocatorInterface $commentLocator */
        $commentLocator = $this->_objectManager->create(CommentLocatorInterface::class);
        $comments = $commentLocator->getListForQuote($quote->getId());
        $comment = reset($comments);
        if ($comment === false) {
            return false;
        }

        $attachments = $comment->getAttachments();

        return reset($attachments);
    }

    /**
     * @param string $message
     * @return string
     */
    private function getMessageText(string $message): string
    {
        return htmlentities($message, ENT_QUOTES | ENT_SUBSTITUTE);
    }

    /**
     * Assign customer to company.
     *
     * @param CustomerInterface $customer
     * @param CompanyInterface $company
     * @return void
     */
    private function assignCustomerToCompany(CustomerInterface $customer, CompanyInterface $company): void
    {
        $companyCustomerAttributes = $this->companyAttributesFactory->create();
        $companyCustomerAttributes->setCustomerId($customer->getId());
        $companyCustomerAttributes->setCompanyId($company->getId());
        $customer->getExtensionAttributes()->setCompanyAttributes($companyCustomerAttributes);
        $this->customerRepository->save($customer);
    }

    /**
     * Assign role to customer.
     *
     * @param CustomerInterface $customer
     * @param RoleModel $role
     * @return void
     * @throws NoSuchEntityException
     */
    private function assignRoleToCustomer(CustomerInterface $customer, RoleModel $role): void
    {
        $this->aclRoleManager->assignRoles($customer->getId(), [$role]);
    }

    /**
     * Moves a company user to a team within the company structure.
     *
     * @param CustomerInterface $manager
     * @param CustomerInterface $customer
     * @return void
     * @throws LocalizedException
     */
    private function moveCompanyUser(CustomerInterface $manager, CustomerInterface $customer)
    {
        $this->_objectManager->removeSharedInstance(StructureTree::class);
        /** @var StructureManager $structureManager */
        $structureManager = $this->_objectManager->create(StructureManager::class);

        $managerStructure = $structureManager->getStructureByCustomerId($manager->getId());
        $customerStructure = $structureManager->getStructureByCustomerId($customer->getId());
        $structureManager->moveNode($customerStructure->getId(), $managerStructure->getId(), true);
    }
}
