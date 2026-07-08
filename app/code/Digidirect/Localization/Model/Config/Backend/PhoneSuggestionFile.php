<?php

namespace Digidirect\Localization\Model\Config\Backend;

use Digidirect\Localization\Model\PhoneSuggestionImport;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;

class PhoneSuggestionFile extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @var PhoneSuggestionImport
     */
    protected $phoneSuggestionImport;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * PhoneSuggestionFile constructor.
     * @param PhoneSuggestionImport $phoneSuggestionImport
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        PhoneSuggestionImport $phoneSuggestionImport,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->phoneSuggestionImport = $phoneSuggestionImport;
        $this->messageManager = $messageManager;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function afterCommitCallback()
    {
        if (!empty($this->getFileData())) {
            $result = $this->phoneSuggestionImport->import($this->getValue(), $this->getScope(), $this->getScopeId());
            if ($result === true) {
                $this->messageManager->addSuccessMessage(__('File has been successfully imported'));
            } else {
                $this->messageManager->addErrorMessage(__($result));
            }
        }
        return parent::afterCommitCallback();
    }
}
