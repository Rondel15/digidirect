<?php

namespace Digidirect\Collect\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class Abstract Action
 *
 * @package Digidirect\Collect\Controller
 */
abstract class AbstractAction extends Action
{
    /**
     * Collect Helper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * StorageHandler
     *
     * @var \Digidirect\Collect\Model\StorageHandler
     */
    protected $_storageHandler;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * AbstractAction constructor.
     *
     * @param Context $context
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     * @param \Digidirect\Collect\Model\StorageHandler $storageHandler
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Digidirect\Collect\Helper\Data $collectHelper,
        \Digidirect\Collect\Model\StorageHandler $storageHandler,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_collectHelper = $collectHelper;
        $this->_storageHandler = $storageHandler;
        $this->_logger = $logger;
    }
}
