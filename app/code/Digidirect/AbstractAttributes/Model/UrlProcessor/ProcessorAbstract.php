<?php
namespace Digidirect\AbstractAttributes\Model\UrlProcessor;

use Magento\Framework\UrlFactory as MagentoUrlFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Digidirect\AbstractAttributes\Helper\Url as UrlHelper;
use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;

/**
 * Class ProcessorAbstract
 * @package Digidirect\AbstractAttributes\Model\UrlProcessor
 */
class ProcessorAbstract
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var AbstractAttributeRepositoryInterface
     */
    protected $abstractAttributeRepository;

    /**
     * @var OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * ProcessorAbstract constructor.
     * @param StoreManagerInterface $storeManager
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param StorageInterface $storage
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param UrlHelper $urlHelper
     * @param AbstractAttributeRepositoryInterface $abstractAttributeRepository
     * @param OptionRepositoryInterface $optionRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlRewriteFactory $urlRewriteFactory,
        StorageInterface $storage,
        MagentoUrlFactory $urlFactory,
        UrlHelper $urlHelper,
        AbstractAttributeRepositoryInterface $abstractAttributeRepository,
        OptionRepositoryInterface $optionRepository
    ) {
        $this->storeManager = $storeManager;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->storage = $storage;
        $this->urlFactory = $urlFactory;
        $this->urlHelper = $urlHelper;
        $this->abstractAttributeRepository = $abstractAttributeRepository;
        $this->optionRepository = $optionRepository;
    }
}
