<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Company\Query\Resolver;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Company\Api\RoleRepositoryInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Company\Api\Data\RoleInterface;

/**
 * Test company hierarchy resolver
 */
class CompanyStructureTest extends GraphQlAbstract
{
    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $customerAuthenticationHeader;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->idEncoder = $objectManager->get(Uid::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->roleRepository = $objectManager->get(RoleRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Company/_files/company_with_structure.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testCompanyStructure(): void
    {
        $expected = [
            "structure" => [
                "items" => [
                    [
                        "entity" => [
                            "__typename" => "Customer",
                            "firstname" => "John",
                            "lastname" => "Doe",
                            "email" => "john.doe@example.com"
                        ]
                    ],
                    [
                        "entity" => [
                            "__typename" => "CompanyTeam",
                            "name" => "Test team",
                            "description" => "Test team description"
                        ]
                    ],
                    [
                        "entity" => [
                            "__typename" => "Customer",
                            "firstname" => "Veronica",
                            "lastname" => "Costello",
                            "email" => "veronica.costello@example.com"
                        ]
                    ],
                    [
                        "entity" => [
                            "__typename" => "Customer",
                            "firstname" => "Alex",
                            "lastname" => "Smith",
                            "email" => "alex.smith@example.com"
                        ]
                    ]
                ]
            ]
        ];

        $query = <<<QUERY
{
  company {
    structure {
      items {
        entity {
          __typename
          ... on Customer {
            firstname
            lastname
            email
          }
          ... on CompanyTeam {
            name
            description
          }
        }
      }
    }
  }
}

QUERY;

        $response = $this->executeQuery($query);
        self::assertSame($response['company'], $expected);
    }

    /**
     * @magentoApiDataFixture Magento/Company/_files/company_with_structure.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testCompanyStructureDepth(): void
    {
        $expected = [
            "structure" => [
                "items" => [
                    [
                        "entity" => [
                            "__typename" => "Customer",
                            "firstname" => "John",
                            "lastname" => "Doe",
                            "email" => "john.doe@example.com"
                        ]
                    ]
                ]
            ]
        ];

        $query = <<<QUERY
{
  company {
    structure (depth: 0) {
      items {
        entity {
          __typename
          ... on Customer {
            firstname
            lastname
            email
          }
          ... on CompanyTeam {
            name
            description
          }
        }
      }
    }
  }
}
QUERY;

        $response = $this->executeQuery($query);
        self::assertSame($response['company'], $expected);
    }

    /**
     * @param $query
     * @return array|bool|float|int|string
     * @throws AuthenticationException
     */
    private function executeQuery($query)
    {
        return $this->graphQlQuery(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute('john.doe@example.com', 'password')
        );
    }

    /**
     * @magentoApiDataFixture Magento/Company/_files/company_with_structure_and_role.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testActiveInactiveCompanyStructure(): void
    {
        $query = <<<QUERY
{
  company{
    id
    name
    structure{
      items {
        id,
        entity {
          __typename
          ... on Customer {
            firstname
            lastname
            email
          }
          ... on CompanyTeam {
            name
            description
            id
          }
        }
      }
    }
  }
}
QUERY;
        $companyStructureResponseBeforeInactive = $this->executeQuery($query);

        $userId = $this->idEncoder->encode((string)$this->getUserIdByEmail('veronica.costello@example.com'));
        $roleName = 'new custom company role';
        $role = $this->getRoleByName($roleName);
        $roleId = $this->idEncoder->encode((string)$role->getId());
        $mutation = <<<MUTATION
mutation {
  updateCompanyUser(
    input: {
      id: "{$userId}"
      role_id: "{$roleId}"
      status: INACTIVE
    }
  ) {
    user {
      email
      status
    }
  }
}
MUTATION;
        $this->graphQlMutation(
            $mutation,
            [],
            '',
            $this->customerAuthenticationHeader->execute('john.doe@example.com', 'password')
        );
        $companyStructureResponseAfterInactive = $this->executeQuery($query);
        self::assertEquals(
            count($companyStructureResponseBeforeInactive['company']['structure']['items']),
            count($companyStructureResponseAfterInactive['company']['structure']['items'])
        );
    }

    /**
     * Get role object by role name
     *
     * @param string $roleName
     * @return RoleInterface
     * @throws LocalizedException
     */
    private function getRoleByName(string $roleName): RoleInterface
    {
        $this->searchCriteriaBuilder->addFilter('role_name', $roleName);
        /** @var SearchResults $results */
        $results = $this->roleRepository->getList($this->searchCriteriaBuilder->create());
        /** @var RoleInterface[] $items */
        $items = $results->getItems();
        /** @var RoleInterface $team */
        return current(array_values($items));
    }

    /**
     * Get user's id by email
     *
     * @param string $email
     * @return int|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getUserIdByEmail(string $email)
    {
        return $this->customerRepository->get($email)->getId();
    }
}
