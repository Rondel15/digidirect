<?php

namespace Digidirect\Feed\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\FeedFactory;
use Digidirect\Feed\Model\FeedRepository;
use Digidirect\Feed\Model\Feed\Exporter;
use Digidirect\Feed\Model\TemplateFactory;

class Preview extends Save
{
    /**
     * @var Exporter
     */
    protected $exporter;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * Preview constructor.
     * @param Context $context
     * @param FeedFactory $feedFactory
     * @param FeedRepository $feedRepository
     * @param Registry $registry
     * @param TemplateFactory $templateFactory
     * @param Exporter $exporter
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FeedFactory $feedFactory,
        FeedRepository $feedRepository,
        TemplateFactory $templateFactory,
        Exporter $exporter
    ) {
        $this->exporter = $exporter;

        $this->layout = $context->getView()->getLayout();

        parent::__construct($context, $registry, $feedFactory, $feedRepository, $templateFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $feed = $this->initModel();

        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        if ($post = $request->getPostValue('data')) {
            //we receive form values as query string
            parse_str(urldecode($post), $data);

            $data = $this->filterPostData($data);

            $feed->addData($data);
        }

        $contentType = 'text/html';

        try {
            $this->exporter->exportPreview($feed);
            $content = file_get_contents($feed->getPreviewFilePath());

            if ($request->getPostValue()) {
                $content = $this->layout->createBlock('Magento\Backend\Block\Template')
                    ->setTemplate('Digidirect_Feed::feed/preview.phtml')
                    ->setContent($content)
                    ->toHtml();
            } else {
                if ($feed->isXml()) {
                    $contentType = 'application/xml';
                } else {
                    $contentType = 'text/plain';
                }
            }
        } catch (\Exception $e) {
            $content = $e;
        }

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();

        return $response
            ->setHeader('Content-Type', $contentType)
            ->setBody($content);
    }

    public function _processUrlKeys()
    {
        return true;
    }
}
