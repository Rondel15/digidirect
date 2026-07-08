<?php
namespace Digidirect\MyStoreWidget\Plugin\Magento\App\Action;

use Digidirect\MyStoreWidget\Helper\Data as Helper;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class ContextPlugin
 */
class ContextPlugin
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param Helper $helper
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        Helper $helper,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->helper = $helper;
        $this->httpContext = $httpContext;
    }

    /**
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param ResponseInterface|ResultInterface $result
     * @return ResponseInterface|ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        $result
    ) {
        $currentStore = $this->helper->getCurrentStore();
        $this->httpContext->setValue(
            Helper::MYSTORE_COOKIE_NAME,
            ($currentStore ? $currentStore->getId() : false),
            false
        );

        return $result;
    }
}
