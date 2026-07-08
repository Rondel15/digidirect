<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Plumrocket\Newsletterpopup\Block\Popup\Fields;
use Plumrocket\Newsletterpopup\Block\Popup\Integration as BlockIntegration;
use Plumrocket\Newsletterpopup\Model\Popup\GetCurrentByRequest;
use Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder;
use Plumrocket\Newsletterpopup\Model\PopupFactory;
use Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var bool
     */
    protected $_noAnimation = false;

    /**
     * @var bool
     */
    protected $_hasPsloginCall = false;

    /**
     * @var string
     */
    protected $_template = 'templates/prnewsletterpopup-system-template.phtml';

    /**
     * @var bool|\Magento\Catalog\Api\Data\ProductInterface|mixed
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelperFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Variable
     */
    private $templateVariables;

    /**
     * @var \Plumrocket\Newsletterpopup\ViewModel\Popup\Product
     */
    private $popupProduct;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder
     */
    private $variablePlaceholder;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetCurrentByRequest
     */
    private $getCurrentPopup;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\PopupFactory
     */
    private $popupFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer
     */
    private $popupRenderer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context             $context
     * @param \Plumrocket\Newsletterpopup\Helper\Data                      $dataHelper
     * @param \Magento\Cms\Model\Template\FilterProvider                   $filterProvider
     * @param \Magento\Catalog\Api\ProductRepositoryInterface              $productRepository
     * @param \Magento\Framework\Registry                                  $coreRegistry
     * @param \Magento\Catalog\Helper\ImageFactory                         $imageHelperFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface            $priceCurrency
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Variable             $templateVariables
     * @param \Plumrocket\Newsletterpopup\ViewModel\Popup\Product          $popupProduct
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder $variablePlaceholder
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetCurrentByRequest  $getCurrentPopup
     * @param \Plumrocket\Newsletterpopup\Model\PopupFactory               $popupFactory
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Plumrocket\Newsletterpopup\Helper\Data $dataHelper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Plumrocket\Newsletterpopup\Model\Popup\Variable $templateVariables,
        \Plumrocket\Newsletterpopup\ViewModel\Popup\Product $popupProduct,
        Placeholder $variablePlaceholder,
        GetCurrentByRequest $getCurrentPopup,
        PopupFactory $popupFactory,
        Renderer $renderer,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_filterProvider = $filterProvider;
        $this->productRepository = $productRepository;
        $this->coreRegistry = $coreRegistry;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
        $this->templateVariables = $templateVariables;
        $this->popupProduct = $popupProduct;
        $this->variablePlaceholder = $variablePlaceholder;
        $this->getCurrentPopup = $getCurrentPopup;
        $this->popupFactory = $popupFactory;
        $this->popupRenderer = $renderer;
    }

    /**
     * @return \Magento\Framework\View\Element\Template|void
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        $layout = $this->getLayout();

        $this->setChild(
            'popup.fields',
            $layout->createBlock(Fields::class)
        );

        $this->setChild(
            'popup.integration',
            $layout->createBlock(BlockIntegration::class)
        );
    }

    /**
     * @return \Plumrocket\Newsletterpopup\Model\Popup|\Plumrocket\Newsletterpopup\Model\Popup\Theme
     */
    public function getPopup()
    {
        if (! $this->hasData('popup')) {
            try {
                $this->setData('popup', $this->getCurrentPopup->execute());
            } catch (NoSuchEntityException $e) {
                $this->setData('popup', $this->popupFactory->create());
            } catch (NotFoundException $e) {
                $this->setData('popup', $this->popupFactory->create());
            }
        }

        return $this->getData('popup');
    }

    /**
     * @return string
     */
    public function getPopupTemplate()
    {
        $popup = $this->getPopup();
        $code = $popup->getCode();
        $hasContactLists = $this->variablePlaceholder->hasPlaceholder('{{contact_lists}}', $this->getPopup());

        $variablesData = [
            (int) $popup->getId(),
            $popup->getData('text_cancel'),
            $popup->getData('text_title'),
            $popup->getData('text_description'),
            $hasContactLists ? $this->getChildHtml('popup.fields') : $this->getChildHtml(),
            $hasContactLists ? $this->getChildHtml('popup.integration') : '',
            $popup->getData('text_submit'),
        ];

        $variablesData = $this->popupProduct->addVariables($variablesData, $this);

        $code = str_replace(
            $this->templateVariables->getVariablesCode(),
            $variablesData,
            $code
        );

        $code = str_replace('.newspopup_up_bg', '#newspopup_up_bg_'.$popup->getId(), $code);

        if (false !== mb_strpos($code, 'window.psLogin')) {
            $this->_hasPsloginCall = true;
        }

        return $this->_filterProvider->getPageFilter()->filter($code);
    }

    /**
     * @param $placeholder
     * @return bool
     */
    public function hasPlaceholder($placeholder)
    {
        return $this->variablePlaceholder->hasPlaceholder($placeholder, $this->getPopup());
    }

    /**
     * @return mixed|null|string|string[]
     */
    public function getPopupStyle()
    {
        return $this->popupRenderer->buildCss($this->getPopup());
    }

    /**
     * @return $this
     */
    public function noAnimation()
    {
        $this->_noAnimation = true;

        return $this;
    }

    /**
     * @return string
     */
    public function getAnimation()
    {
        return $this->_noAnimation ? '' : $this->getPopup()->getAnimation();
    }

    /**
     * @return bool
     */
    public function hasPsloginCall()
    {
        return $this->_hasPsloginCall;
    }

    /**
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct()
    {
        if (null === $this->product) {
            try {
                if (!$this->getPopup()->getIsTemplate() && $this->getPopup()->useCurrentProduct()) {
                    $currentProduct = $this->coreRegistry->registry('current_product');

                    if ($currentProduct && $currentProduct->getId()) {
                        $this->product = $currentProduct;
                    } elseif ($this->_request->getParam('productId')) {
                        $this->product = $this->productRepository->getById($this->_request->getParam('productId'));
                    }
                }

                if ((! $this->product || ! $this->product->getId()) && $this->getPopup()->getDefaultProduct()) {
                    $this->product = $this->productRepository->get($this->getPopup()->getDefaultProduct());
                }
            } catch (NoSuchEntityException $e) {
                $this->product = false;
            }
        }

        return $this->product;
    }
}
