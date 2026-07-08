<?php

namespace Digidirect\SEO\Controller;

use Digidirect\SEO\Helper\ContextualRedirects as ContextualRedirectsHelper;
use Digidirect\SEO\Model\RedirectProcessorInterface;
use Digidirect\SEO\Model\Router\PathsUpdaterRegistry;
use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class RecursiveContextualRedirectRouter
 *
 * @package Digidirect\SEO\Controller
 */
class RecursiveContextualRedirectRouter implements RouterInterface
{
    /**
     * Permanent redirect code
     */
    const PERMANENT = 301;

    const SUFFIX_DELIMITER = '.';
    const URL_DELIMITER = '/';

    /**
     * @var PathsUpdaterRegistry
     */
    protected $pathsUpdaterRegistry;

    /**
     * @var ContextualRedirectsHelper
     */
    protected $contextualRedirectsHelper;

    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    protected $suffix;

    /**
     * @var RedirectProcessorInterface
     */
    protected $redirectProcessor;

    /**
     * RecursiveContextualRedirectRouter constructor.
     *
     * @param PathsUpdaterRegistry       $pathsUpdaterRegistry
     * @param ContextualRedirectsHelper  $contextualRedirectsHelper
     * @param StoreManagerInterface      $storeManager
     * @param UrlFinderInterface         $urlFinder
     * @param RedirectProcessorInterface $redirectProcessor
     */
    public function __construct(
        PathsUpdaterRegistry $pathsUpdaterRegistry,
        ContextualRedirectsHelper $contextualRedirectsHelper,
        StoreManagerInterface $storeManager,
        UrlFinderInterface $urlFinder,
        RedirectProcessorInterface $redirectProcessor
    ) {
        $this->pathsUpdaterRegistry = $pathsUpdaterRegistry;
        $this->contextualRedirectsHelper = $contextualRedirectsHelper;
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
        $this->redirectProcessor = $redirectProcessor;
    }

    /**
     * @param RequestInterface $request
     *
     * @return null|ActionInterface
     * @throws NoSuchEntityException
     */
    public function match(RequestInterface $request)
    {
        if (!$this->contextualRedirectsHelper->isContextualRedirectsEnabled()) {
            return null;
        }

        $paths = $this->preparePathsByRequest($request);
        foreach ($this->pathsUpdaterRegistry->getUpdaters() as $updater) {
            $paths = $updater->update($paths);
        }
        if (!$paths) {
            return null;
        }

        return $this->redirectProcessor->processRedirect(
            $request,
            $this->prepareRedirectUrl($paths, $request),
            self::PERMANENT
        );
    }

    /**
     * @param array            $paths
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function prepareRedirectUrl(array $paths, RequestInterface $request)
    {
        return implode(self::URL_DELIMITER, $paths) . $this->getRequestSuffix($request);
    }

    /**
     * @param RequestInterface $request
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function preparePathsByRequest(RequestInterface $request)
    {
        /** @var Request $request */
        if(empty($request->getPathInfo()))
        {
            return '';
        }
        $path = trim($request->getPathInfo(), self::URL_DELIMITER);
        $path = $this->clarifyRequestPath($path);
        $this->setRequestSuffix($path);
        $parts = array_filter(explode(self::URL_DELIMITER, $path));
        array_pop($parts);

        return $parts;
    }

    /**
     * @param string $path
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function clarifyRequestPath(string $path)
    {
        $store = $this->storeManager->getStore();
        if (!$store) {
            return $path;
        }

        $requestPath = $this->urlFinder->findOneByData(
            [
                UrlRewrite::TARGET_PATH => $path,
                UrlRewrite::STORE_ID => $store->getId(),
            ]
        );

        if (!$requestPath) {
            return $path;
        }

        return $requestPath->getRequestPath();
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function getRequestSuffix(RequestInterface $request)
    {
        if ($this->suffix) {
            return $this->suffix;
        }
        /** @var Request $request */
        if(empty($request->getPathInfo()))
        {
            return '';
        }
        $path = trim($request->getPathInfo(), self::URL_DELIMITER);
        $delimiterSuffixPosition = strpos($path, self::SUFFIX_DELIMITER);

        if (!$delimiterSuffixPosition) {
            return '';
        }

        return substr($path, $delimiterSuffixPosition);
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    protected function setRequestSuffix(string $path)
    {
        $delimiterSuffixPosition = strpos($path, self::SUFFIX_DELIMITER);
        $this->suffix = '';
        if ($delimiterSuffixPosition > 0) {
            $this->suffix = substr($path, $delimiterSuffixPosition);
        }

        return $this;
    }
}
