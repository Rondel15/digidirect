<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NegotiableQuote\Controller\Adminhtml;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\NegotiableQuote\Controller\Adminhtml\Quote\PrintAction;

/**
 * @magentoDataFixture Magento/NegotiableQuote/_files/negotiable_quote_with_company_and_customer.php
 * @magentoDbIsolation enabled
 * @magentoConfigFixture default_store btob/website_configuration/negotiablequote_active true
 * @magentoAppArea adminhtml
 */
class PrintActionTest extends AbstractTest
{
    /**
     * @var string
     */
    private $uriTemplate = 'backend/quotes/quote/print/quote_id/%s';

    /**
     * {@inheritDoc}
     * @var string
     */
    protected $resource = PrintAction::ADMIN_RESOURCE;

    /**
     * @inheritDoc
     */
    public function testAclHasAccess()
    {
        $this->uri = $this->interpolateUriWithQuoteId($this->uriTemplate);
        parent::testAclHasAccess();
    }

    /**
     * @inheritDoc
     */
    public function testAclNoAccess()
    {
        $this->uri = $this->interpolateUriWithQuoteId($this->uriTemplate);
        parent::testAclNoAccess();
    }

    /**
     * Test Quote print
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testQuotePrint(): void
    {
        $this->uri = $this->interpolateUriWithQuoteId($this->uriTemplate);
        $this->dispatch($this->uri);
        $content = $this->getResponse()->getBody();
        $this->assertStringContainsString(
            'window.print();',
            $content
        );
    }

    /**
     * Test Quote Print no logo should be displayed unless specified
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testQuotePrintNoLogo(): void
    {
        $this->uri = $this->interpolateUriWithQuoteId($this->uriTemplate);
        $this->dispatch($this->uri);
        $content = $this->getResponse()->getBody();
        $logoBlock = '<div class="negotiable-quote-print-logo">';
        $this->assertStringNotContainsString(
            $logoBlock,
            $content
        );
    }

    /**
     * Test Quote Print Logo Should be used from sales/identity/logo_html with priority
     * over design/header/logo_src configuration value
     *
     * @magentoConfigFixture default_store sales/identity/logo_html default/logo_sales.jpg
     * @magentoConfigFixture default_store design/header/logo_src default/logo.jpg
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testQuotePrintLogoFromIdentityHtml(): void
    {
        $this->uri = $this->interpolateUriWithQuoteId($this->uriTemplate);
        $this->dispatch($this->uri);
        $content = $this->getResponse()->getBody();
        $logoBlock = <<<EOD
<div class="negotiable-quote-print-logo">
    <img class="logo-img" src="http://localhost/media/sales/store/logo_html/default/logo_sales.jpg"
         alt="Adobe Commerce Admin Panel"
         title="Adobe Commerce Admin Panel"/>
</div>
EOD;

        $this->assertStringContainsString(
            $logoBlock,
            $content
        );
    }

    /**
     * Test Quote Print Logo Should be used from design/header/logo_src configuration value
     *
     * @magentoConfigFixture default_store design/header/logo_src default/logo.jpg
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testQuotePrintLogoFromDesignConfiguration(): void
    {
        $this->uri = $this->interpolateUriWithQuoteId($this->uriTemplate);
        $this->dispatch($this->uri);
        $content = $this->getResponse()->getBody();
        $logoBlock = <<<EOD
<div class="negotiable-quote-print-logo">
    <img class="logo-img" src="http://localhost/media/logo/default/logo.jpg"
         alt="Adobe Commerce Admin Panel"
         title="Adobe Commerce Admin Panel"/>
</div>
EOD;

        $this->assertStringContainsString(
            $logoBlock,
            $content
        );
    }
}
