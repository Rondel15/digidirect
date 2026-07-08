<?php

namespace Digidirect\SEO\Controller;

use Digidirect\SEO\Helper\ContextualRedirects as ContextualRedirectsHelper;
use Digidirect\SEO\Model\RedirectProcessorInterface;
use Digidirect\SEO\Model\Router\PathsUpdaterRegistry;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class ContextualRedirectRouter
 *
 * @package Digidirect\SEO\Controller
 */
class ContextualRedirectRouter implements RouterInterface
{
    /**
     * Permanent redirect code
     */
    const PERMANENT = 301;

    const URL_DELIMITER = '/';

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
    protected $entityType;

    /**
     * @var PathsUpdaterRegistry
     */
    protected $pathsUpdaterRegistry;

    /**
     * @var ContextualRedirectsHelper
     */
    protected $contextualRedirectsHelper;

    /**
     * @var RedirectProcessorInterface
     */
    protected $redirectProcessor;

    /**
     * ContextualRedirectRouter constructor.
     *
     * @param UrlFinderInterface         $urlFinder
     * @param StoreManagerInterface      $storeManager
     * @param PathsUpdaterRegistry       $pathsUpdaterRegistry
     * @param ContextualRedirectsHelper  $contextualRedirectsHelper
     * @param RedirectProcessorInterface $redirectProcessor
     * @param string                     $entityType
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        PathsUpdaterRegistry $pathsUpdaterRegistry,
        ContextualRedirectsHelper $contextualRedirectsHelper,
        RedirectProcessorInterface $redirectProcessor,
        string $entityType
    ) {
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
        $this->pathsUpdaterRegistry = $pathsUpdaterRegistry;
        $this->contextualRedirectsHelper = $contextualRedirectsHelper;
        $this->redirectProcessor = $redirectProcessor;
        $this->entityType = $entityType;
    }

    /**
     * @param RequestInterface $request
     *
     * @return null|ActionInterface
     */
    public function match(RequestInterface $request)
    {
        if (!$this->contextualRedirectsHelper->isContextualRedirectsEnabled()) {
            return null;
        }

        $paths = $this->preparePathsByRequest($request);

        try {
            foreach ($this->pathsUpdaterRegistry->getUpdaters() as $updater) {
                $paths = $updater->update($paths);
            }
            if (!$paths) {
                return null;
            }

            $urlRewrites = $this->findUrlRewrites(array_filter($paths));
            if (!$urlRewrites) {
                return null;
            }

            foreach ($paths as $path) {
                if (isset($urlRewrites[$path])) {
                    //Perform redirect
                    return $this->redirectProcessor->processRedirect($request, $path, self::PERMANENT);
                }
            }
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return null;
    }

    /**
     * @param array $paths
     *
     * @return UrlRewrite[]|array
     * @throws NoSuchEntityException
     */
    protected function findUrlRewrites(array $paths)
    {
        $store = $this->storeManager->getStore();
        if (!$store) {
            return [];
        }

        $urlRewrites = $this->urlFinder->findAllByData(
            [
                UrlRewrite::REQUEST_PATH => $paths,
                UrlRewrite::STORE_ID => $store->getId(),
                UrlRewrite::ENTITY_TYPE => $this->entityType,
            ]
        );

        foreach ($urlRewrites as $key => $urlRewrite) {
            unset($urlRewrites[$key]);
            $urlRewrites[$urlRewrite->getRequestPath()] = $urlRewrite;
        }

        return $urlRewrites;
    }

    /**
     * @param RequestInterface $request
     *
     * @return array
     */
    protected function preparePathsByRequest(RequestInterface $request)
    {
        if(empty($request->getPathInfo()))
        {
            return '';
        }
        /** @var Request $request */
        $path = trim($request->getPathInfo(), self::URL_DELIMITER);
        $parts = array_filter(explode(self::URL_DELIMITER, $path));
        array_pop($parts);

        $preparedParts = [];
        // convert paths from ['a','b','c'] to ['a/b/c', 'a/b', 'a']
        while ($parts) {
            $preparedParts[] = implode(self::URL_DELIMITER, $parts);
            array_pop($parts);
        }

        return $preparedParts;
    }
}
