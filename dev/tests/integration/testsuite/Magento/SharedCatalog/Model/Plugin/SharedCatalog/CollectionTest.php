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

namespace Magento\SharedCatalog\Model\Plugin\SharedCatalog;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Test\Fixture\Role as AdminRole;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection as SharedCatalogCollection;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Store\Test\Fixture\Group as GroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Bootstrap;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use Magento\User\Model\UserFactory;
use Magento\User\Test\Fixture\User;
use PHPUnit\Framework\TestCase;

#[
    AppArea(Area::AREA_ADMINHTML)
]
class CollectionTest extends TestCase
{

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var SharedCatalogCollection
     */
    private $collection;

    /**
     * @var Session
     */
    private $backendAuthSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtures = BootstrapHelper::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->userFactory = BootstrapHelper::getObjectManager()->create(UserFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->backendAuthSession->clearStorage();
    }

    /**
     * @throws LocalizedException
     */
    #[
        DataFixture(WebsiteFixture::class, as: 'w2'),
        DataFixture(GroupFixture::class, ['website_id' => '$w2.id$'], 'g2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$g2.id$'], 's2'),
        DataFixture(AdminRole::class, [
            'gws_is_all' => 0,
            'gws_websites' => '$w2.id$',
            'gws_store_groups' => '$g2.id$'
        ], as: 'websiteRole'),
        DataFixture(User::class, ['role_id' => '$websiteRole.id$'], as: 'websiteUser'),
        DataFixture(AdminRole::class, [
            'gws_is_all' => 0, 'gws_store_groups' => '$g2.id$'
        ], as: 'storeGroupRole'),
        DataFixture(User::class, ['role_id' => '$storeGroupRole.id$'], as: 'storeGroupUser'),
        DataFixture(AdminRole::class, [
            'gws_is_all' => 0,
            'gws_websites' => '1,'.'$w2.id$',
            'gws_store_groups' => '1,'.'$g2.id$'
        ], as: 'allWebsitesRole'),
        DataFixture(User::class, ['role_id' => '$storeGroupRole.id$'], as: 'allWebsitesUser'),
        DataFixture(SharedCatalog::class, [
            'store_id' => null,
            'created_by' => '$allWebsitesUser.id$'
        ], 'sharedCatalog1'),
        DataFixture(SharedCatalog::class, ['store_id' => 1], 'sharedCatalog2'),
        DataFixture(SharedCatalog::class, ['store_id' => '$g2.id$'], 'sharedCatalog2')
    ]
    public function testUserWithRestrictedWebsiteAndStoreGroup()
    {
        $this->runTestFor('storeGroupUser', 'storeGroupRole');
        $this->runTestFor('websiteUser', 'websiteRole');
        $this->runTestFor('allWebsitesUser', 'allWebsitesRole');

        $this->backendAuthSession->clearStorage();
    }

    /**
     * Run the same test for each restricted user
     *
     * @param string $user
     * @param string $role
     * @return void
     * @throws LocalizedException
     */
    private function runTestFor(string $user, string $role): void
    {
        $user = $this->fixtures->get($user);
        $role = $this->fixtures->get($role);

        $this->loginAsAdminUser($user, $role);

        $this->collection = BootstrapHelper::getObjectManager()->create(SharedCatalogCollection::class);
        $items = $this->collection->load();

        $this->assertEquals(1, $items->getSize());

        $this->backendAuthSession->clearStorage();
    }

    /**
     * @param \Magento\User\Model\User $user
     * @param Role $role
     * @return void
     * @throws LocalizedException
     */
    private function loginAsAdminUser(\Magento\User\Model\User $user, Role $role): void
    {
        $username = $user->getDataByKey('username');
        $adminUser = $this->userFactory->create();
        $adminUser->setRole($role);
        $adminUser->login($username, Bootstrap::ADMIN_PASSWORD);
        $this->backendAuthSession = BootstrapHelper::getObjectManager()->get(Session::class);
        $this->backendAuthSession->setUser($adminUser);
        $this->backendAuthSession->processLogin();
    }
}
