<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Config;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ShellInterface;
use Plumrocket\Base\Controller\Adminhtml\Actions;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;

class Refresh extends Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_Newsletterpopup::config';

    protected $_adminhtmlHelper;
    protected $_cache;

    /**
     * @var \Magento\Framework\ShellInterface
     */
    private $shell;

    public function __construct(
        Context $context,
        Adminhtml $adminhtmlHelper,
        Cache $cache,
        ShellInterface $shell
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_cache = $cache;
        parent::__construct($context);
        $this->shell = $shell;
    }

    public function execute()
    {
        $result = [
            'error'   => true,
            'message' => 'Wkhtmltoimage was not found. Please contact your webserver ' .
                'admin to install thumbnail generation tool.',
        ];

        // already found
        if ($this->_adminhtmlHelper->checkIfHtmlToImageInstalled()) {
            $result['error'] = false;
            $result['message'] = 'Wkhtmltoimage is already configured. Thumbnail generation is Enabled.';
        } else {
            $cacheKeyName = $this->_adminhtmlHelper->getHtmlToImageCacheKeyName();
            $path = '/sbin /usr/sbin /usr/local/bin ~';

            try {
                $resPath = $this->shell->execute("find $path -name \"wkhtmltoimage\"");
                if ($resPath) {
                    $this->_cache->save($resPath, $cacheKeyName, [], 86400 * 365 * 40);
                    $result['error'] = false;
                    $result['message'] = 'Wkhtmltoimage has been found. Thumbnail generation is now Enabled.';
                }
            } catch (LocalizedException $e) {
                $result['message'] = $e->getMessage();
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }
}
