<?php
namespace Digidirect\Utilities\Preference\Magento\Cms\Controller\Adminhtml\Wysiwyg;

use Magento\Backend\App\Action;
use Magento\Cms\Model\Template\Filter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Mime;

class Directive extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive
{
    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Mime
     */
    protected $mime;

    /**
     * @var array
     */
    protected $xmlImageTypes = [];

    /**
     * Directive constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param DirectoryList $directoryList
     * @param Mime $mime
     * @param array $xmlImageTypes
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        DirectoryList $directoryList,
        Mime $mime,
        array $xmlImageTypes = []
    ) {
        parent::__construct($context, $urlDecoder, $resultRawFactory);
        $this->directoryList = $directoryList;
        $this->mime = $mime;
        $this->xmlImageTypes = $xmlImageTypes;
    }

    /**
     * Template directives callback
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $directive = $this->getRequest()->getParam('___directive');
        $directive = $this->urlDecoder->decode($directive);
        $imagePath = $this->_objectManager->create(Filter::class)->filter($directive);
        $file = $this->directoryList->getPath(DirectoryList::PUB) . '/' . $imagePath;

        try {
            $mime = $this->mime->getMimeType($file);
            if (in_array($mime, $this->xmlImageTypes)) {
                $resultRaw = $this->resultRawFactory->create();
                $resultRaw->setHeader('Content-Type', $mime);
                $resultRaw->setContents(file_get_contents($file));
                return $resultRaw;
            }
            throw new LocalizedException(__('Wrong XML image type.'));
        } catch (LocalizedException $e) {
            return parent::execute();
        }
    }
}
