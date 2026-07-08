<?php
namespace Digidirect\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Upload
 */
class Upload extends \Magento\Backend\App\Action
{
    /**
     * @var \Digidirect\Blog\Model\ImageProcessor
     */
    protected $imageProcessor;

    /**
     * Upload constructor.
     * @param Context $context
     * @param \Digidirect\Blog\Model\ImageProcessor $imageProcessor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Digidirect\Blog\Model\ImageProcessor $imageProcessor
    ) {
        parent::__construct($context);
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $files = $this->getRequest()->getFiles();
            $result = $this->imageProcessor->saveFileToTmpDir($files['image']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_Blog::blogpost');
    }
}
