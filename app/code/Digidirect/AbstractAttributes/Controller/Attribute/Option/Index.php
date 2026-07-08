<?php
namespace Digidirect\AbstractAttributes\Controller\Attribute\Option;

use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Digidirect\AbstractAttributes\Model\Layer\Option as LayerOption;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package Digidirect\AbstractAttributes\Controller\Attribute\Option
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Catalog session
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
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
     * @var OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param OptionRepositoryInterface $optionRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        OptionRepositoryInterface $optionRepository
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->catalogSession = $catalogSession;
        $this->coreRegistry = $coreRegistry;
        $this->layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->optionRepository = $optionRepository;
    }

    /**
     * @return OptionInterface|false
     * @throws LocalizedException
     */
    protected function _initOption()
    {
        $optionId = (int)$this->getRequest()->getParam('_option');
        try {
            $option = $this->optionRepository->getByOptionId($optionId, $this->storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }

        if (!$option->getStatus()) {
            return false;
        }

        $this->coreRegistry->register('current_eaa_option', $option);
        return $option;
    }

    /**
     * Initialize requested category object
     * @return \Magento\Catalog\Api\Data\CategoryInterface|false
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
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return false;
        }

        return $category;
    }

    /**
     * Category view action
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $option = $this->_initOption();
        $category = $this->_initCategory();
        if ($category && $option) {
            /** @var \Magento\Framework\View\Result\Page $page */
            $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $this->layerResolver->create(LayerOption::LAYER_NAME);

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

            if ($option->getListing()) {
                $page->addHandle('eaa_attribute_option_listing');
            } else {
                $page->addHandle('eaa_attribute_option_landing');
            }

            if ($pageLayout = $option->getLayoutUpdate()) {
                $page->getConfig()->setPageLayout($pageLayout);
            }

            $page->addHandle(['type' => 'DIGIDIRECT_AA_OPTION_' . $option->getId()]);
            if ($layoutUpdate = trim($option->getLayoutUpdateXml())) {
                $page->addUpdate($layoutUpdate);
            }

            $this->_eventManager->dispatch(
                'controller_action_layout_render_before_eaa_option_view',
                ['eaa_option' => $option]
            );

            return $page;
        }

        return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
    }
}
