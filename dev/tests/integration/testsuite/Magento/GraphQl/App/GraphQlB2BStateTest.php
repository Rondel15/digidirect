<?php
/************************************************************************
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
 ***********************************************************************/
declare(strict_types=1);

namespace Magento\GraphQl\App;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\GraphQl\App\State\GraphQlStateDiff;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests the dispatch method in the GraphQl Controller class using a simple product query
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea graphql
 */
class GraphQlB2BStateTest extends TestCase
{
    /**
     * @var GraphQlStateDiff|null
     */
    private ?GraphQlStateDiff $graphQlStateDiff = null;

    /**
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setUp(): void
    {
        if (!class_exists(\Magento\GraphQl\App\State\GraphQlStateDiff::class)) {
            $this->markTestSkipped('GraphQlStateDiff class is not available on this version of Magento.');
        } else {
            $reflection = new ReflectionClass(\Magento\GraphQl\App\State\GraphQlStateDiff::class);
            $constructor = $reflection->getConstructor();
            $parameters = $constructor->getParameters();

            // This condition is added to preserve for backward compatibility with 2.4.7-p4-develop versions.
            if (count($parameters)) {
                // phpstan:ignore
                $this->graphQlStateDiff = new \Magento\GraphQl\App\State\GraphQlStateDiff($this);
            } else {
                // phpstan:ignore
                $this->graphQlStateDiff = new \Magento\GraphQl\App\State\GraphQlStateDiff();
            }
            /** @var $configWriter WriterInterface */
            $configWriter = $this->graphQlStateDiff->getTestObjectManager()->get(WriterInterface::class);

            $path = 'btob/website_configuration/company_active';
            $configWriter->save($path, 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
            $path = 'company/general/allow_company_registration';
            $configWriter->save($path, 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
        }
        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->graphQlStateDiff->tearDown();
        $this->graphQlStateDiff = null;
        parent::tearDown();
    }

    public static function queryDataProvider(): array
    {
        return [
            'Is Company Admin Email Available' => [
                <<<'QUERY'
                    query {
                        isCompanyAdminEmailAvailable(email: "admin@magento.com") {
                            is_email_available
                        }
                    }
                    QUERY,
                [],
                [],
                [],
                'isCompanyAdminEmailAvailable',
                'isCompanyAdminEmailAvailable'
            ],
            'Is Company User email available' => [
                <<<'QUERY'
                    query {
                        isCompanyUserEmailAvailable(email: "email@adobe.com") {
                        is_email_available
                        }
                    }
                    QUERY,
                [],
                [],
                [],
                'isCompanyUserEmailAvailable',
                'is_email_available'

            ],
            'Create Company' => [
                <<<'QUERY'
                     mutation($name: String!, $email: String!) {
                          createCompany(
                            input: {
                              company_name: $name
                              company_email: $email
                              legal_name: "Legalname"
                              vat_tax_id: "12345"
                              reseller_id: "123"
                              company_admin:   {
                                email: $email
                                firstname: "Company"
                                lastname: "Admin"
                                gender: 1
                                job_title: "Manager"
                                telephone: "12345"
                              }
                              legal_address: {
                                city: "City"
                                country_id: US
                                postcode: "12345"
                                region: {
                                    region_id: 35
                                }
                                street: ["Street  123"]
                                telephone: "0123456789"
                              }
                            }
                          ) {
                            company {
                              id
                              email
                            }
                          }
                    }
                    QUERY,
                ['name' => 'Company1', 'email' => 'email1@magento.com'],
                ['name' => 'Company2', 'email' => 'email2@magento.com'],
                [],
                'createCompany',
                '"email":"'
            ],
            'Is Company email available' => [
                <<<'QUERY'
                    query {
                        isCompanyEmailAvailable(email: "email@adobe.com") {
                        is_email_available
                        }
                    }
                    QUERY,
                [],
                [],
                [],
                'isCompanyEmailAvailable',
                'is_email_available'

            ],
        ];
    }

    /**
     * Test state change for B2B queries
     *
     * @dataProvider queryDataProvider
     * @return void
     */
    public function testState(
        string $query,
        array $variables,
        array $variables2,
        array $authInfo,
        string $operationName,
        string $expected
    ): void {
        $this->markTestSkipped('Fix this later');
        $this->graphQlStateDiff
            ->testState($query, $variables, $variables2, $authInfo, $operationName, $expected, $this);
    }
}
