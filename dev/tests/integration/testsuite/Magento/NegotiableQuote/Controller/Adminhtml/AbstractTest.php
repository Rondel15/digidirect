<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NegotiableQuote\Controller\Adminhtml;

use Magento\NegotiableQuote\Model\Company\DetailsProviderFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

abstract class AbstractTest extends AbstractBackendController
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRespository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerRespository = $this->_objectManager->create(CustomerRepositoryInterface::class);
        $this->quoteRepository = $this->_objectManager->create(CartRepositoryInterface::class);
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getQuoteCreatedInFixture()
    {
        $customer = $this->customerRespository->get('email@companyquote.com');
        $quote = $this->quoteRepository->getForCustomer($customer->getId());

        return $quote;
    }

    /**
     * Dynamically assign $uri using quote id from data fixture
     *
     * @param string $uriTemplate
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function interpolateUriWithQuoteId($uriTemplate)
    {
        return sprintf($uriTemplate, $this->getQuoteCreatedInFixture()->getId());
    }

    /**
     * Assert that quote is created for company with given email.
     *
     * @param int $quoteId
     * @param string $companyEmail
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assertQuoteCompany(int $quoteId, string $companyEmail)
    {
        /** @var CartRepositoryInterface $cartRepoInterface */
        $cartRepoInterface = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $cart = $cartRepoInterface->get($quoteId);

        /** @var DetailsProviderFactory $companyProviderFactory */
        $companyProviderFactory = Bootstrap::getObjectManager()->get(DetailsProviderFactory::class);
        $detailsProvider = $companyProviderFactory->create(
            ['quote' => $cart]
        );
        $company = $detailsProvider->getCompany();

        $this->assertEquals(
            $companyEmail,
            $company->getCompanyEmail(),
            'Quote is expected to be created for customer\'s default company in multi-company assignment.'
        );
    }
}
