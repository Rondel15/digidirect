<?php
namespace Digidirect\AddressVerification\Controller\Aupost;

use Digidirect\AddressVerification\Api\CountryAddressAttributeRepositoryInterface;

/**
 * Class Search
 * @package Digidirect\AddressVerification\Controller\Aupost
 */
class Search extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Digidirect\AddressVerification\Helper\Aupost
     */
    protected $aupostHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CountryAddressAttributeRepositoryInterface
     */
    protected $countryAddressAttributeRepository;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $httpResponse;

    /**
     * @var int
     */
    protected $cacheTtl;

    /**
     * Search constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Digidirect\AddressVerification\Helper\Aupost $aupostHelper
     * @param CountryAddressAttributeRepositoryInterface $countryAddressAttributeRepository
     * @param \Magento\Framework\App\Response\Http $httpResponse
     * @param int $cacheTtl
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Digidirect\AddressVerification\Helper\Aupost $aupostHelper,
        CountryAddressAttributeRepositoryInterface $countryAddressAttributeRepository,
        \Magento\Framework\App\Response\Http $httpResponse,
        $cacheTtl = 604800
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->aupostHelper = $aupostHelper;
        $this->storeManager = $storeManager;
        $this->countryAddressAttributeRepository = $countryAddressAttributeRepository;
        $this->httpResponse = $httpResponse;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $postcode = $this->getRequest()->getParam('postcode');
        $suburb = $this->getRequest()->getParam('suburb');
        $region = $this->getRequest()->getParam('region');
        $countryCode = $this->getRequest()->getParam('country_code');
        $result = $this->aupostHelper->searchLocation($postcode, $suburb, $region, $countryCode);

        $this->httpResponse->setPublicHeaders($this->cacheTtl);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
