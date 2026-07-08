<?php
namespace Digidirect\AbstractAttributes\Block\Attribute\Option;

use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Asset\GroupedCollection as PageAsset;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class View
 * @package Digidirect\AbstractAttributes\Block\Attribute\Option
 */
class View extends \Magento\Framework\View\Element\Template
{
    const CACHE_TAG = 'eaa_attribute_option_view';
    const ASSET_CANONICAL = 'canonical';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Digidirect\AbstractAttributes\Helper\Url
     */
    protected $urlHelper;

    /**
     * @var \Digidirect\AbstractAttributes\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var PageAsset
     */
    protected $pageAsset;

    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;

    /**
     * View constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Digidirect\AbstractAttributes\Helper\Url $urlHelper
     * @param \Digidirect\AbstractAttributes\Helper\Data $helper
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array $data
     * @param PageAsset $pageAsset
     * @param CategoryHelper $categoryHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Digidirect\AbstractAttributes\Helper\Url $urlHelper,
        \Digidirect\AbstractAttributes\Helper\Data $helper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        array $data,
        PageAsset $pageAsset = null,
        CategoryHelper $categoryHelper = null
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->urlHelper = $urlHelper;
        $this->helper = $helper;
        $this->filterProvider = $filterProvider;
        $this->blockFactory = $blockFactory;
        $this->pageAsset = $pageAsset ?: ObjectManager::getInstance()->get(PageAsset::class);
        $this->categoryHelper = $categoryHelper ?: ObjectManager::getInstance()->get(CategoryHelper::class);
        parent::__construct($context, $data);
    }

    /**
     * @return OptionInterface
     */
    public function getOption()
    {
        if (!$this->hasData('option')) {
            $this->setData('option', $this->coreRegistry->registry('current_eaa_option'));
        }
        return $this->getData('option');
    }

    /**
     * @return string
     */
    public function getCmsBlockContent()
    {
        $blockId = $this->getOption()->getCmsBlock();
        $html = '';
        if ($blockId) {
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var \Magento\Cms\Model\Block $block */
            $block = $this->blockFactory->create();
            $block->setStoreId($storeId);
            $block->getResource()->load($block, $blockId);
            if ($block->isActive()) {
                $html = $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
            }
        }
        return $html;
    }

    /**
     * @param OptionInterface $option
     * @throws LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs(OptionInterface $option)
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
            ]);
            $attribute = $option->getAttribute();
            if ($attribute->getListingEnabled()) {
                $breadcrumbsBlock->addCrumb('eaa', [
                    'label' => $attribute->getAttributeLabel(),
                    'title' => $attribute->getAttributeLabel(),
                    'link' => $this->urlHelper->getAttributeUrl($attribute)
                ]);
            }
            $breadcrumbsBlock->addCrumb('eaa_option', [
                'label' => $option->getLabel(),
                'title' => $option->getLabel()
            ]);
        }
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $option = $this->getOption();
        if (!$option) {
            return parent::_prepareLayout();
        }

        $this->_addBreadcrumbs($option);
        $this->pageConfig->addBodyClass('eaa-option-' . $option->getOptionId());
        $this->pageConfig->addBodyClass('eaa-' . $option->getAttribute()->getAttributeCode());

        $metaTitle = $option->getMetaTitle();
        if (!$metaTitle) {
            //$metaTitle = $option->getLabel();
            $metaTitle = "Buy ".$option->getLabel(). " | digiDirect";
        }
        $this->pageConfig->getTitle()->set($metaTitle);

        $metaDesc = $option->getMetaDesc();
        if (!$metaDesc) {
            //$metaDesc = $option->getDescription();
            $metaDesc = "Shop ".$option->getLabel()." online at digiDirect - the camera, computer & electrical experts. Afterpay Available.";
        }
        $this->pageConfig->setDescription($this->helper->prepareMetaDescription($metaDesc));

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->escapeHtml($option->getLabel()));
        }

        if ($this->categoryHelper->canUseCanonicalTag()) {
            $this->addCanonicalUrl();
        }

        return parent::_prepareLayout();
    }

    /**
     * @return void
     */
    public function addCanonicalUrl()
    {
        foreach ($this->pageAsset->getAll() as $url => $asset) {
            if ($asset->getContentType() == self::ASSET_CANONICAL) {
                $this->pageAsset->remove($url);
            }
        }

        $option = $this->getOption();
        $this->pageConfig->addRemotePageAsset(
            'canonical-aa',
            self::ASSET_CANONICAL,
            ['attributes' => ['rel' => self::ASSET_CANONICAL]]
        );
    }

    /**
     * @param OptionInterface $option
     * @return string
     */
    public function getCanonicalUrl(OptionInterface $option)
    {
        return $option->getUrl();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $option = $this->getOption();
        if (!$option) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getOption()->getId()];
    }
}
