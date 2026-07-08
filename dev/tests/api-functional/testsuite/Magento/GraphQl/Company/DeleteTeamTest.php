<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Company;

use Magento\Company\Api\Data\TeamInterface;
use Magento\Company\Api\TeamRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\AssignCustomer;
use Magento\Company\Api\Data\TeamInterfaceFactory;
use Magento\Company\Model\Company\Structure;

/**
 * Test class for Team delete action.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTeamTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getCustomerAuthenticationHeader;

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var TeamRepositoryInterface $teamRespoitory
     */
    private $teamRepository;

    /**
     * Setup
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getCustomerAuthenticationHeader = $this->objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->teamRepository = $this->objectManager->get(TeamRepositoryInterface::class);
    }

    /**
     * Tests unauthorized access
     *
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testUnauthorizedCustomer()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Customer is not a company user.');

        $mutation = <<<MUTATION
mutation {
  deleteCompanyTeam(
    id: "..."
  ) {
    success
  }
}
MUTATION;

        $this->graphQlMutation($mutation);
    }

    /**
     * Delete team by ID
     *
     * @magentoApiDataFixture Magento/Company/_files/company_with_teams.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testDelete()
    {
        $team = $this->findTeamByName('Team A (level 1)');
        $teamId = base64_encode($team->getId());
        $mutation = <<<MUTATION
mutation {
  deleteCompanyTeam(
    id: "{$teamId}"
  ) {
    success
  }
}
MUTATION;

        $response = $this->graphQlMutation(
            $mutation,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute('customer@example.com', 'password')
        );

        $this->assertNotEmpty($response['deleteCompanyTeam']['success']);
        $this->assertTrue($response['deleteCompanyTeam']['success']);
    }

    /**
     * Attempt unauthorized delete team by ID
     *
     * @magentoApiDataFixture Magento/Company/_files/company_with_teams.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testUnauthorizedDelete()
    {
        $team = $this->findTeamByName('Team A (level 1)');
        $teamId = $team->getId() + 10;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Value of uid "' . $teamId . '" is incorrect');

        $mutation = <<<MUTATION
mutation {
  deleteCompanyTeam(
    id: "{$teamId}"
  ) {
    success
  }
}
MUTATION;

        $this->graphQlMutation(
            $mutation,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute('customer@example.com', 'password')
        );
    }

    /**
     * Find one team by name
     *
     * @param string $name
     * @return TeamInterface
     * @throws LocalizedException
     */
    private function findTeamByName($name)
    {
        $this->searchCriteriaBuilder->addFilter('name', $name);
        /** @var SearchResults $results */
        $results = $this->teamRepository->getList($this->searchCriteriaBuilder->create());
        /** @var TeamInterface[] $items */
        $items = $results->getItems();
        /** @var TeamInterface $team */
        $team = array_values($items)[0];
        return $team;
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'company_admin'),
        DataFixture(
            Company::class,
            [
                'status' => 1,
                'super_user_id' => '$company_admin.id$'
            ],
            'company'
        ),
        DataFixture(Customer::class, as: 'team_user'),
        DataFixture(AssignCustomer::class, ['company_id' => '$company.id$', 'customer_id' => '$team_user.id$'])
    ]
    /**
     * Validates error message for delete team action with inactive user
     */
    public function testDeleteTeamWithActiveUser(): void
    {
        $customer = DataFixtureStorageManager::getStorage()->get('company_admin');
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $teamUser = DataFixtureStorageManager::getStorage()->get('team_user');

        $teamRepository = $this->objectManager->get(TeamRepositoryInterface::class);
        $teamFactory = $this->objectManager->get(TeamInterfaceFactory::class);
        $structureManagement = $this->objectManager->create(Structure::class);

        $team1 = $teamFactory->create();
        $team1->setName('Team 1');
        $teamRepository->create($team1, $company->getId());
        $teamId = base64_encode($team1->getId());

        $structureTeam1 = $structureManagement->getStructureByTeamId($team1->getId());
        $structureTeamUser = $structureManagement->getStructureByCustomerId($teamUser->getId());
        $structureManagement->moveNode($structureTeamUser->getId(), $structureTeam1->getId());

        $mutation = <<<MUTATION
mutation {
  deleteCompanyTeam(
    id: "{$teamId}"
  ) {
    success
  }
}
MUTATION;

        $caughtException = null;
        $expectedError = "Cannot delete team with id $teamId."
            . ' This team has active users or teams assigned to it and cannot be deleted.'
            . ' Please unassign the users or teams first.';
        try {
            $this->graphQlMutation(
                $mutation,
                [],
                '',
                $this->getCustomerAuthenticationHeader->execute($customer->getEmail(), 'password')
            );
        } catch (ResponseContainsErrorsException $exception) {
            $caughtException = $exception;
        }
        $this->assertInstanceOf(
            ResponseContainsErrorsException::class,
            $caughtException
        );
        $this->assertStringContainsString(
            $expectedError,
            $caughtException->getMessage()
        );
    }
}
