<?php

namespace Digidirect\SEO\Model;

use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;

/**
 * Class RedirectProcessor
 *
 * @package Digidirect\SEO\Model
 */
class RedirectProcessor implements RedirectProcessorInterface
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * RedirectProcessor constructor.
     *
     * @param ResponseInterface $response
     * @param ActionFactory     $actionFactory
     * @param UrlInterface      $url
     */
    public function __construct(
        ResponseInterface $response,
        ActionFactory $actionFactory,
        UrlInterface $url
    ) {
        $this->response = $response;
        $this->actionFactory = $actionFactory;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function processRedirect(RequestInterface $request, string $url, int $code)
    {
        $url = $this->url->getUrl($url);
        $this->response->setRedirect($url, $code);
        $request->setDispatched(true);

        return $this->actionFactory->create(Redirect::class);
    }
}
