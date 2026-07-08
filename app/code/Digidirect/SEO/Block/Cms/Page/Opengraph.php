<?php

namespace Digidirect\SEO\Block\Cms\Page;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Framework\View\Element\Template\Context;

class Opengraph extends \Magento\Framework\View\Element\Template
{
    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var PageHelper
     */
    private $pageHelper;

    /**
     * Opengraph constructor.
     * @param PageRepositoryInterface $pageRepository
     * @param PageHelper $pageHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        PageHelper $pageHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageRepository = $pageRepository;
        $this->pageHelper = $pageHelper;
    }

    /**
     * Get current page
     *
     * @return \Magento\Cms\Api\Data\PageInterface|null
     */
    public function getCurrentPage()
    {
        $pageId = $this->getRequest()->getParam('page_id', $this->getRequest()->getParam('id', false));
        if ($pageId) {
            try {
                return $this->pageRepository->getById($pageId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Get page helper
     *
     * @return PageHelper
     */
    public function getPageHelper()
    {
        return $this->pageHelper;
    }
}
