<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Company\Query;

use Magento\Company\Api\Data\TeamInterface;
use Magento\Company\Model\Company\Structure;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\Team;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;

/**
 * Test for structure_id fields
 */
class StructureIdTest extends GraphQlAbstract
{
    private const QUERY_CUSTOMER = <<<QRY
{
    company {
        user(id: "%s") {
            structure_id
        }
        team(id: "%s") {
            structure_id
        }
    }
}
QRY;

    private const QUERY_COMPANY_TEAM = <<<QRY2
{
  company {
    name
    structure {
     items {
     entity {
      __typename
      ... on Customer {
        firstname
        lastname
        email
        structure_id
      }
      ... on CompanyTeam {
         name
         structure_id
      }
    }
 }
}
}
}
QRY2;

    private const QUERY_NONCOMPANY_CUSTOMER = <<<QRY3
{
    customer {
    firstname
    lastname
    structure_id
    }
}
QRY3;

    /**
     * @var Uid
     */
    private $uid;

    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getHeader;

    /**
     * @var Structure
     */
    private $structure;

    protected function setUp(): void
    {
        $this->uid = Bootstrap::getObjectManager()->get(Uid::class);
        $this->getHeader = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class);
        $this->structure = Bootstrap::getObjectManager()->get(Structure::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer.id$'
            ],
            'company'
        ),
        DataFixture(Team::class, ['company_id' => '$company.id$'], 'team')
    ]
    public function testCustomer(): void
    {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        /** @var TeamInterface $team */
        $team = DataFixtureStorageManager::getStorage()->get('team');

        $this->assertEquals(
            [
                'company' => [
                    'user' => [
                        'structure_id' => $this->uid->encode(
                            (string)$this->structure->getStructureByCustomerId($customer->getId())->getId()
                        )
                    ],
                    'team' => [
                        'structure_id' => $this->uid->encode(
                            (string)$this->structure->getStructureByTeamId($team->getId())->getId()
                        )
                    ]
                ]
            ],
            $this->graphQlQuery(
                sprintf(
                    self::QUERY_CUSTOMER,
                    $this->uid->encode((string)$customer->getId()),
                    $this->uid->encode((string)$team->getId())
                ),
                [],
                '',
                $this->getHeader->execute($customer->getEmail())
            )
        );
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer.id$'
            ],
            'company'
        ),
        DataFixture(Team::class, ['company_id' => '$company.id$'], 'team')
    ]
    public function testCompanyTeamId(): void
    {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        /** @var TeamInterface $team */
        $team = DataFixtureStorageManager::getStorage()->get('team');
        /** @var Company $company */
        $company = DataFixtureStorageManager::getStorage()->get('company');

        $expected = [
            'name' => $company->getCompanyName(),
            'structure' => [
                'items' => [
                    [
                        'entity' => [
                            '__typename' => 'Customer',
                            'firstname' => $customer->getFirstname(),
                            'lastname' => $customer->getLastname(),
                            'email' => $customer->getEmail(),
                            'structure_id' => $this->uid->encode(
                                (string)$this->structure->getStructureByCustomerId($customer->getId())->getId()
                            )
                        ]
                    ],
                    [
                        'entity' => [
                            '__typename' => 'CompanyTeam',
                            'name' => $team->getName(),
                            'structure_id' => $this->uid->encode(
                                (string)$this->structure->getStructureByTeamId($team->getId())->getId()
                            )
                        ]
                    ]
                ]
            ]
        ];
        $response = $this->graphQlQuery(
            sprintf(
                self::QUERY_COMPANY_TEAM
            ),
            [],
            '',
            $this->getHeader->execute($customer->getEmail())
        );
        self::assertSame($expected, $response['company']);
    }

    /**
     * @throws \Exception
     */
    #[
        Datafixture(Customer::class, as: 'nonCompanyCustomer'),
    ]

    public function testNonCompanyCustomer(): void
    {
        self::expectExceptionMessage('Could not retrieve customer information.');
        $nonCompanyCustomer = FixtureManager::getStorage()->get('nonCompanyCustomer');

        $this->graphQlQuery(
            self::QUERY_NONCOMPANY_CUSTOMER,
            [],
            '',
            $this->getHeader->execute($nonCompanyCustomer->getEmail())
        );
    }
}
