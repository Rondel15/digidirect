<?php
namespace Digidirect\LayeredNavigation\Plugin\Framework;

use Digidirect\LayeredNavigation\Helper\Meta;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultInterface;

class CategoryView
{
    /**
     * @var Meta
     */
    protected $metaHelper;

    /**
     * CategoryView constructor.
     * @param Meta $metaHelper
     */
    public function __construct(Meta $metaHelper)
    {
        $this->metaHelper = $metaHelper;
    }

    /**
     * @param Action $subject
     * @param Page $result
     * @return ResultInterface
     */
    public function afterExecute(Action $subject, $result)
    {
        if ($result instanceof Page) {
            $this->metaHelper->setPageTags($result->getConfig());
        }
        return $result;
    }
}
