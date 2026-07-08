<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic\Category;

use Digidirect\Feed\Controller\Adminhtml\Dynamic\Category;
use Digidirect\Feed\Model\Dynamic\CategoryFactory;
use Digidirect\Feed\Helper\CategoryMapping\ReaderMapper;
use Digidirect\Feed\Helper\CategoryMapping\Multiplicity\FileReaderMultiplicity;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\ForwardFactory;

class Search extends Category
{
    /**
     * @var ReaderMapper
     */
    protected $readerMapper;

    /**
     * @var FileReaderMultiplicity
     */
    protected $fileReaderMultiplicity;

    /**
     * Search constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param CategoryFactory $categoryFactory
     * @param ReaderMapper $readerMapper
     * @param FileReaderMultiplicity $fileReaderMultiplicity
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        CategoryFactory $categoryFactory,
        ReaderMapper $readerMapper,
        FileReaderMultiplicity $fileReaderMultiplicity
    ) {
        $this->readerMapper = $readerMapper;
        $this->fileReaderMultiplicity = $fileReaderMultiplicity;
        parent::__construct($context, $registry, $resultForwardFactory, $categoryFactory);
    }

    /**
     * Do search of category.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $search = $this->getRequest()->getParam('query');

        $this->fileReaderMultiplicity->findAll();
        if ($this->fileReaderMultiplicity->count()) {
            $this->readerMapper->addMultiplicity($this->fileReaderMultiplicity);
        }

        $resultPage->setData($this->readerMapper->getData($search));

        return $resultPage;
    }
}
