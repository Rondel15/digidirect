<?php

namespace Digidirect\MarketplacerSeller\Plugin\Controller\Seller;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\StoreManagerInterface;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Config as ConfigHelper;
use Marketplacer\Seller\Model\Layer\Seller;

/**
 * Class View
 * @package Marketplacer\Seller\Controller\Seller
 */
class View extends Action
{
    const LAYER_NAME = Seller::LAYER_NAME;

    /**
     * Core registry
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * Catalog session
     * @var Session
     */
    protected $catalogSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var Resolver
     */
    protected $layerResolver;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    
    protected $logger;

    /**
     * Index constructor.
     * @param Context $context
     * @param Session $catalogSession
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SellerRepositoryInterface $sellerRepository
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        SellerRepositoryInterface $sellerRepository,
        ConfigHelper $configHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->catalogSession = $catalogSession;
        $this->coreRegistry = $coreRegistry;
        $this->layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->sellerRepository = $sellerRepository;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
    }

    /**
     * @return false|SellerInterface
     */
    protected function _initSeller()
    {
        $sellerId = (int)$this->getRequest()->getParam('seller_id');
        $this->logger->info('$sellerId: ' . $sellerId);
        $storeId = $this->storeManager->getStore()->getId();
        try {
            $seller = $this->sellerRepository->getById($sellerId, $storeId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        if (!$seller->isEnabled()) {
            return false;
        }
        $this->coreRegistry->register('current_seller', $seller);
        return $seller;
    }

    /**
     * @return false|CategoryInterface
     * @throws NoSuchEntityException
     */
    protected function _initCategory()
    {
        $store = $this->storeManager->getStore();
        $categoryId = $store->getRootCategoryId();
        if (!$categoryId) {
            return false;
        }

        try {
            $category = $this->categoryRepository->get($categoryId, $store->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }

        $this->catalogSession->setLastVisitedCategoryId($category->getId());
        $this->coreRegistry->register('current_category', $category);
        try {
            $this->_eventManager->dispatch(
                'catalog_controller_category_init_after',
                ['category' => $category, 'controller_action' => $this]
            );
        } catch (LocalizedException $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return false;
        }
        $this->logger->info('$category->getId(): ' . $category->getId());

        return $category;
    }

    public function execute()
    {
        if (!$this->configHelper->isEnabledOnStorefront()) {
            $this->_redirect('noroute');
        }

        $seller = $this->_initSeller();
        $category = $this->_initCategory();
        if ($category && $seller) {
            /** @var Page $page */
            $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $this->layerResolver->create(self::LAYER_NAME);

            $hasChildren = $category->hasChildren();
            if ($category->getIsAnchor()) {
                $type = $hasChildren ? 'layered' : 'layered_without_children';
            } else {
                $type = $hasChildren ? 'default' : 'default_without_children';
            }

            if (!$hasChildren) {
                $parentType = strtok($type, '_');
                $page->addPageLayoutHandles(['type' => $parentType]);
            }
            $page->addPageLayoutHandles(['type' => $type, 'id' => $category->getId()]);
            $this->_eventManager->dispatch(
                'controller_action_layout_render_before_seller_view',
                ['seller' => $seller]
            );

            return $page;
        }

        return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
    }
}
