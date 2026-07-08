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

namespace Magento\CompanyRelation\Adminhtml\Company\CompanyHierarchy;

use Magento\Backend\Model\Auth;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\CompanyRelation\Ui\Component\Hierarchy\AssignChildCompaniesButton;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Builder as AclBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\TestFramework\Bootstrap;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssignChildCompaniesButtonTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Acl
     */
    private $acl;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $origId;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var Auth $auth */
        $this->auth = $this->objectManager->get(Auth::class);
        $this->auth->login(Bootstrap::ADMIN_NAME, Bootstrap::ADMIN_PASSWORD);
        $this->acl = $this->objectManager->get(AclBuilder::class)->getAcl();
        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->origId = $this->request->getParam('id');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->auth->logout();
        $this->request->setParam('id', $this->origId);
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'regular_company_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'parent_company_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_a_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_b_admin'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'sales_rep_user'),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Regular Company',
                CompanyInterface::SUPER_USER_ID => '$regular_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'regular_company'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Parent Company',
                CompanyInterface::SUPER_USER_ID => '$parent_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'parent_company'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company A',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_a'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company B',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_b_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_b'
        ),
        DataFixture(
            \Magento\CompanyRelation\Test\Fixture\AssignRelations::class,
            [
                \Magento\CompanyRelation\Api\Data\RelationInterface::PARENT_ID => '$parent_company.id$',
                'relations' => [
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_a.id$'],
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_b.id$']
                ],
            ],
        ),
    ]
    /**
     * Test that Assign Child Companies button is visible only when the admin user has permission and the company is
     * not child company; otherwise, it is not visible.
     *
     * @param bool $isVisible
     * @param string $fixture
     * @param bool $isAdminAllowed = true
     * @dataProvider buttonVisibilityDataProvider
     */
    public function testButtonVisibility(
        bool $isVisible,
        string $fixture,
        bool $isAdminAllowed = true
    ): void {
        if (!$isAdminAllowed) {
            $this->acl->deny(Bootstrap::ADMIN_ROLE_ID, 'Magento_Company::manage');
        }
        $companyFixture = DataFixtureStorageManager::getStorage()->get($fixture);
        if ($companyFixture) {
            $this->request->setParam('id', $companyFixture->getData(CompanyInterface::COMPANY_ID));
        } else {
            $this->request->setParam('id', null);
        }
        $assignChildCompaniesButton = $this->createAssignChildCompaniesButton();
        $this->assertInstanceOf(AssignChildCompaniesButton::class, $assignChildCompaniesButton);
        $assignChildCompaniesButton->prepare();
        $configuration = $assignChildCompaniesButton->getConfiguration();
        $this->assertEquals($isVisible, $configuration['visible']);
    }

    /**
     * Verify configured button title
     *
     * @return void
     */
    public function testButtonTitle()
    {
        $assignChildCompaniesButton = $this->createAssignChildCompaniesButton();
        $assignChildCompaniesButton->prepare();
        $this->assertEquals('Assign Companies', $assignChildCompaniesButton->getConfiguration()['title']);
    }

    /**
     * @return array
     */
    public static function buttonVisibilityDataProvider(): array
    {
        return [
            'allowed admin can assign child to regular company' => [true, 'regular_company'],
            'not allowed admin cannot assign child to regular company' => [false, 'regular_company', false],
            'allowed admin can assign child to parent company' => [true, 'parent_company'],
            'not allowed admin cannot assign child to parent company' => [false, 'parent_company', false],
            'allowed admin cannot assign child to non-existent company' => [false, 'none'],
            'not allowed admin cannot assign child to non-existent company' => [false, 'none', false],
            'allowed admin cannot assign child to child company' => [false, 'assigned_company_a'],
            'not allowed admin cannot assign child to child company' => [false, 'assigned_company_a', false],
        ];
    }

    /**
     * Get assign child companies button from company_form with respect to existing configuration
     *
     * @return UiComponentInterface
     */
    private function createAssignChildCompaniesButton(): UiComponentInterface
    {
        $context = $this->objectManager->create(ContextInterface::class, ['request' => $this->request]);
        /** @var UiComponentFactory $uiComponentFactory */
        $uiComponentFactory = $this->objectManager->get(UiComponentFactory::class);
        $uiComponent = $uiComponentFactory->create(
            'company_form',
            null,
            ['context' => $context]
        );
        return $uiComponent
            ->getComponent('relation')
            ->getComponent('assign_child_companies_button');
    }
}
