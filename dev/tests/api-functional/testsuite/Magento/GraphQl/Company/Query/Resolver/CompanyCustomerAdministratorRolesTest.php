<?php
/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2024 Adobe
 *  All Rights Reserved.
 *
 *  NOTICE: All information contained herein is, and remains
 *  the property of Adobe and its suppliers, if any. The intellectual
 *  and technical concepts contained herein are proprietary to Adobe
 *  and its suppliers and are protected by all applicable intellectual
 *  property laws, including trade secret and copyright laws.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from Adobe.
 *  ************************************************************************
 */
declare(strict_types=1);

namespace Magento\GraphQl\Company\Query\Resolver;

use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test company roles resolver
 */
class CompanyCustomerAdministratorRolesTest extends GraphQlAbstract
{
    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $customerAuthenticationHeader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
    }

    /**
     * @magentoApiDataFixture Magento/Company/_files/company_with_structure_and_role.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testCompanyCustomerAdministratorRole(): void
    {
        //ID is constant because it's always 0
        $expectedResult['customer']['role'] = [
            'id' => "MA==",
            'name' => "Company Administrator",
            'users_count' => 1,
            'permissions' => []
        ];

        $query = <<<QUERY
{
  customer {
    role {
      id
      name
      users_count
      permissions {
        children {
          id
          sort_order
          text
        }
        id
        sort_order
        text
      }
    }
  }
}
QUERY;

        $this->assertEquals($expectedResult, $this->executeQuery($query, 'john.doe@example.com'));
    }

    /**
     * @magentoApiDataFixture Magento/Company/_files/company_with_structure_and_role.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testCompanyCustomerRole(): void
    {
        $query = <<<QUERY
{
  customer {
    role {
      id
      name
      users_count
      permissions {
        children {
          id
          sort_order
          text
          children {
              id
              sort_order
              text
            }
        }
        id
        sort_order
        text
      }
    }
  }
}
QUERY;

        $this->validateAclResource(
            $this->executeQuery($query, 'veronica.costello@example.com')['customer']['role']['permissions']
        );
    }

    /**
     * @param array $aclResources
     */
    private function validateAclResource(array $aclResources): void
    {
        foreach ($aclResources as $aclResource) {
            self::assertArrayHasKey('id', $aclResource);
            self::assertArrayHasKey('sort_order', $aclResource);
            self::assertArrayHasKey('text', $aclResource);

            if (!empty($aclResource['children'])) {
                $this->validateAclResource($aclResource['children']);
            }
        }
    }

    /**
     * @param string $query
     * @param string $email
     * @return array|bool|float|int|string
     */
    private function executeQuery(string $query, string $email)
    {
        return $this->graphQlQuery(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($email)
        );
    }
}
