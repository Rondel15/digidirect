<?php

namespace Digidirect\ProductOverlay\Controller\Adminhtml;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Ui\Component\MassAction\Filter;
use Digidirect\ProductOverlay\Helper\Timezone;

/**
 * Abstract Class Overlays
 * @package Digidirect\ProductOverlay\Controller\Adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Overlays extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Date filter instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Digidirect\ProductOverlay\Model\OverlaysFactory
     */
    protected $_overlayFactory;

    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $_overlayHelper;

    /**
     * @var \Digidirect\ProductOverlay\Model\RuleFactory
     */
    protected $_overlayRuleFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_file;

    /**
     * @var \Digidirect\ProductOverlay\Model\OverlaysRepository
     */
    protected $_overlayRepository;

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var \Digidirect\ProductOverlay\Model\ResourceModel\Overlays\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $typeList;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var Timezone
     */
    protected $timezone;

    /**
     * Overlays constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Digidirect\ProductOverlay\Model\OverlaysFactory $overlayFactory
     * @param \Digidirect\ProductOverlay\Helper\Data $overlayHelper
     * @param \Digidirect\ProductOverlay\Model\RuleFactory $overlayRuleFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Digidirect\ProductOverlay\Model\OverlaysRepository $overlayRepository
     * @param \Digidirect\ProductOverlay\Model\ResourceModel\Overlays\CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param array $data
     * @param Timezone $timezone
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Date $dateFilter,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Digidirect\ProductOverlay\Model\OverlaysFactory $overlayFactory,
        \Digidirect\ProductOverlay\Helper\Data $overlayHelper,
        \Digidirect\ProductOverlay\Model\RuleFactory $overlayRuleFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem\Io\File $file,
        \Digidirect\ProductOverlay\Model\OverlaysRepository $overlayRepository,
        \Digidirect\ProductOverlay\Model\ResourceModel\Overlays\CollectionFactory $collectionFactory,
        Filter $filter,
        \Magento\Framework\App\Cache\TypeListInterface $typeList,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        array $data = [],
        Timezone $timezone = null
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory    = $resultPageFactory;
        $this->_dateFilter          = $dateFilter;
        $this->_filesystem          = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_overlayFactory      = $overlayFactory;
        $this->_overlayHelper       = $overlayHelper;
        $this->_overlayRuleFactory  = $overlayRuleFactory;
        $this->_logger              = $logger;
        $this->_file                = $file;
        $this->_overlayRepository   = $overlayRepository;
        $this->_filter              = $filter;
        $this->typeList = $typeList;
        $this->serializer = $serializer;
        $this->data = $data;
        $this->timezone = $timezone ?: ObjectManager::getInstance()->get(Timezone::class);
    }

    /**
     * Initiate action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Digidirect_ProductOverlay::overlay')
            ->_addBreadcrumb(__('Product Overlays'), __('Product Overlays'));
        return $this;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_ProductOverlay::overlay');
    }

    /**
     * @return \Digidirect\ProductOverlay\Helper\Data
     */
    protected function _getOverlayHelper()
    {
        return $this->_overlayHelper;
    }
}
