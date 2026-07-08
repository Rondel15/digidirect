<?php
namespace Digidirect\Blog\Controller;

use Digidirect\Blog\Api\CategoryRepositoryInterface;
use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Model\UrlModel;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url;

/**
 * Class Router
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Router implements RouterInterface
{
    const MODULE_NAME = 'blog';

    const LIST_CONTROLLER = 'index';

    const CATEGORY_CONTROLLER = 'category';

    const TAG_CONTROLLER = 'tag';

    const ARCHIVE_CONTROLLER = 'archive';

    const VIEW_POST_CONTROLLER = 'view';

    const ACTION = 'index';

    /**
     * @var bool
     */
    protected $dispatched;
    
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var UrlModel
     */
    protected $urlModel;

    /**
     * Router constructor.
     * @param ActionFactory $actionFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param ResponseInterface $response
     * @param CategoryRepositoryInterface $categoryRepository
     * @param PostRepositoryInterface $postRepository
     * @param Data $dataHelper
     * @param UrlModel $urlModel
     */
    public function __construct(
        ActionFactory $actionFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        ResponseInterface $response,
        CategoryRepositoryInterface $categoryRepository,
        PostRepositoryInterface $postRepository,
        Data $dataHelper,
        UrlModel $urlModel
    ) {
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->dataHelper = $dataHelper;
        $this->urlModel = $urlModel;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function match(RequestInterface $request)
    {
        if (!$this->dispatched) {
            if (!$this->dataHelper->isModuleEnabled()) {
                return null;
            }
            $urlKey = trim($request->getPathInfo(), '/');
            $origUrlKey = $urlKey;
            /** @var Object $condition */
            $condition = new DataObject(
                ['url_key' => $urlKey, 'continue' => true]
            );
            $this->eventManager->dispatch(
                'digidirect_blog_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );
            $urlKey = $condition->getUrlKey();

            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create('Magento\Framework\App\Action\Redirect', ['request' => $request]);
            }

            if (!$condition->getContinue()) {
                return null;
            }

            if ($this->urlModel->getBlogListUrl(false) == $urlKey) {
                return $this->setRedirect($request, self::LIST_CONTROLLER, $urlKey);
            }

            $parts = explode('/', $urlKey);
            $urlKey = $this->prepareUrlKey($urlKey, $parts);

            if ($this->isCategoryRoot($urlKey)) {
                return $this->setRedirect($request, self::LIST_CONTROLLER, $urlKey);
            }

            if ($this->isBlogIndexRoute($urlKey)) {
                return $this->setRedirect($request, self::LIST_CONTROLLER, $urlKey);
            }

            if ($urlKeyCat = $this->getCategoryPath($origUrlKey, $parts)) {
                $catId = $this->categoryRepository->getCategoryIdByUrlKey(
                    $urlKeyCat,
                    $this->storeManager->getStore()->getId()
                );
                if ($catId) {
                    return $this->setRedirect(
                        $request,
                        self::CATEGORY_CONTROLLER,
                        $origUrlKey,
                        ['category_id', $catId]
                    );
                }
            }
            if (isset($parts[0]) && $parts[0] == self::ARCHIVE_CONTROLLER && isset($parts[1])) {
                return $this->setRedirect($request, self::ARCHIVE_CONTROLLER, $origUrlKey, ['date', $parts[1]]);
            }
            if ($tag = $this->getTag($urlKey)) {
                return $this->setRedirect($request, self::TAG_CONTROLLER, $origUrlKey, ['tag', $tag]);
            }

            $postId = null;
            if (isset($parts[1])) {
                $postId = $this->postRepository->getPostIdByUrlKey($parts[1], $this->storeManager->getStore()->getId());
            }
            if ($postId) {
                return $this->setRedirect($request, self::VIEW_POST_CONTROLLER, $origUrlKey, ['post_id', $postId]);
            }
        }
        return null;
    }

    /**
     * @param string $urlKey
     *
     * @return bool
     */
    protected function isCategoryRoot(string $urlKey)
    {
        return $urlKey === $this->dataHelper->getGeneralSettingsConfig('cat_prefix');
    }

    /**
     * @param string $urlKey
     *
     * @return bool
     */
    protected function isBlogIndexRoute(string $urlKey)
    {
        return $urlKey === $this->dataHelper->getGeneralSettingsConfig('list_url');
    }

    /**
     * @param RequestInterface $request
     * @param string $urlKey
     * @param string $controller
     * @param array $params
     * @return \Magento\Framework\App\ActionInterface
     */
    protected function setRedirect(RequestInterface $request, $controller, $urlKey, $params = [])
    {
        $request->setModuleName(self::MODULE_NAME)
            ->setControllerName($controller)
            ->setActionName(self::ACTION);
        if (!empty($params)) {
            list($paramName, $paramValue) = $params;
            $request->setParam($paramName, $paramValue);
        }
        $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
        $request->setDispatched(true);
        $this->dispatched = true;
        return $this->actionFactory->create('Magento\Framework\App\Action\Forward', ['request' => $request]);
    }

    /**
     * @param string $urlKey
     * @param array $parts
     * @return string
     */
    protected function prepareUrlKey($urlKey, array $parts)
    {
        $urlPrefix = $this->dataHelper->getGeneralSettingsConfig('url_prefix');
        $urlSuffix = $this->dataHelper->getGeneralSettingsConfig('url_suffix');
        $categoryPrefix = $this->dataHelper->getGeneralSettingsConfig('cat_prefix');

        if ($urlPrefix) {
            if (count($parts) == 2 && ($parts[0] == $categoryPrefix || $parts[0] == $urlPrefix)) {
                $urlKey = $parts[1];
            }
        }
        if ($urlSuffix) {
            $suffix = substr($urlKey, -strlen($urlSuffix) - 1);
            if ($suffix == '.' . $urlSuffix) {
                $urlKey = substr($urlKey, 0, -strlen($urlSuffix) - 1);
            }
        }
        return $urlKey;
    }

    /**
     * @param string $origUrlKey
     * @param array $parts
     * @return bool|string
     */
    protected function getCategoryPath($origUrlKey, array $parts)
    {
        $categoryPrefix = $this->dataHelper->getGeneralSettingsConfig('cat_prefix');
        $categorySuffix = $this->dataHelper->getGeneralSettingsConfig('url_suffix');
        $urlKeyPart = false;
        if ($categoryPrefix) {
            $catPrefix = explode('/', $origUrlKey);
            if (!empty($parts) && $parts[0] == $categoryPrefix) {
                if (!empty($urlSuffix)) {
                    $urlKeyPart = substr($catPrefix[1], 0, -strlen($urlSuffix) - 1);
                } else {
                    if (empty($catPrefix[1])) {
                        return null;
                    }
                    $urlKeyPart = $catPrefix[1];
                }
            }
        }
        return str_replace('.' . $categorySuffix, '', $urlKeyPart);
    }

    /**
     * @param string $urlKey
     * @return null|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getTag($urlKey)
    {
        $urlPieces = explode('/', $urlKey);
        if (count($urlPieces) == 3) {
            list($module, $controller, $tag) = $urlPieces;
            if ($controller == self::TAG_CONTROLLER && !empty($tag)) {
                return $tag;
            }
        }
        return null;
    }
}
