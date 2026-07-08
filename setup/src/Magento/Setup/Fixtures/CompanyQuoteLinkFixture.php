<?php
/**
 * ADOBE CONFIDENTIAL
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Fixtures;

use Magento\Framework\App\ResourceConnection;

/**
 * Fixture generator for Company To Quote links.
 */
class CompanyQuoteLinkFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 333;

    /**
     * @var ResourceConnection
     */
    private $resources;

    /**
     * @var string
     */
    private $quoteConnectionName = 'checkout';

    /**
     * @param FixtureModel $fixtureModel
     * @param ResourceConnection $resources
     */
    public function __construct(
        FixtureModel $fixtureModel,
        ResourceConnection $resources
    ) {
        $this->resources = $resources;
        parent::__construct($fixtureModel);
    }

    /**
     * Generate links between company and quotes.
     *
     * @return void
     */
    public function execute()
    {
        $isCompanyToQuoteLinks = (bool)$this->fixtureModel->getValue('company_quote_link', true);
        if (!$isCompanyToQuoteLinks) {
            return;
        }
        $connectionInstance = $this->resources->getConnection($this->quoteConnectionName);
        $companyCustomerEntityTable = $this->resources->getTableName('company_advanced_customer_entity');
        $quoteTable = $this->resources->getTableName('quote');
        $companyQuoteLinksSelect = $connectionInstance->select()
            ->from(
                ['company_advanced_customer_entity' => $companyCustomerEntityTable],
                ['company_id' => 'company_advanced_customer_entity.company_id']
            )->joinInner(
                ['quote' => $quoteTable],
                'quote.customer_id = company_advanced_customer_entity.customer_id',
                ['quote_id' => 'quote.entity_id']
            );
        $insertFromSelectQuery = $connectionInstance->insertFromSelect(
            $companyQuoteLinksSelect,
            'company_quote_link',
            ['company_id', 'quote_id']
        );
        $connectionInstance->query($insertFromSelectQuery);
    }

    /**
     * @inheritDoc
     */
    public function getActionTitle()
    {
        return 'Generating links between company and quotes';
    }

    /**
     * @inheritDoc
     */
    public function introduceParamLabels()
    {
        return ['company_quote_link' => 'Company Quote Link'];
    }
}
