<?php
namespace ZV\SeoCompatible\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\Registry;

class Hreflang extends Template
{
    protected $registry;
    protected $catalogHelper;

    public function __construct(
        Template\Context $context,
        CatalogHelper $catalogHelper,
        Registry $registry,
        array $data = []
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getCurrentUrl()
    {
        /*$product = $this->registry->registry('current_product');
        if ($product) {
            $currentUrl = $this->catalogHelper->getProductUrl($product);
        }*/
        
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        
        $urlComponents = parse_url($currentUrl);
                
        $canonical = $urlComponents['scheme'] . '://' . $urlComponents['host'] . $urlComponents['path'];

        if (!empty($urlComponents['query'])) {
                    
            $this->pageConfig->setRobots("NOINDEX,NOFOLLOW");

            parse_str($urlComponents['query'], $params);

            if (!empty($params['p'])) {

                if (count($params) == 1) {
                    $this->pageConfig->setRobots("INDEX,FOLLOW");
                }

                if ($params['p'] == 1) {
                    $page = ''; 
                } else {
                    $page = '?p=' . $params['p']; 
                }

                $canonical = $urlComponents['scheme'] . '://' . $urlComponents['host'] . $urlComponents['path'] . $page;
            } else {
                $canonical = $urlComponents['scheme'] . '://' . $urlComponents['host'] . $urlComponents['path'];
            }
        }
        
        
        return $canonical;
    }
}
