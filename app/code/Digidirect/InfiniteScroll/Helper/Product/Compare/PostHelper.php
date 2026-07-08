<?php
namespace Digidirect\InfiniteScroll\Helper\Product\Compare;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\Json\Helper\Data as Json;

class PostHelper extends \Magento\Framework\Data\Helper\PostHelper
{
    /**
     * @var Json
     */
    protected $json;

    /**
     * PostHelper constructor.
     * @param Context $context
     * @param UrlHelper $urlHelper
     * @param Json $json
     */
    public function __construct(
        Context $context,
        UrlHelper $urlHelper,
        Json $json
    ) {
        parent::__construct($context, $urlHelper);
        $this->json = $json;
    }

    /**
     * get data for post by javascript in format acceptable to $.mage.dataPost widget
     *
     * @param string $url
     * @param array $data
     * @return string
     */
    public function getPostData($url, array $data = [])
    {
        return $this->json->jsonEncode(['action' => $url, 'data' => $data]);
    }
}
