<?php
namespace Digidirect\AI\Model\Lib\Backup;

/**
 * Class BackupFactory
 * @package Digidirect\AI\Model\Lib\Backup
 */
class BackupFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\\Digidirect\\AI\\Model\\Lib\\Backup\\Backup'
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function create(array $data = [])
    {
        if (empty($data['archiveType']['instance'])) {
            $data['archiveType']['instance'] = '\Digidirect\AI\Model\Lib\Archive\Tar';
        }

        $archive = $this->_objectManager->create($data['archiveType']['instance']);
        if (!($archive instanceof \Magento\Framework\Archive\ArchiveInterface)) {
            throw new \Exception(
                'Wrong archive type, class is not implement Magento\Framework\Archive\Archive Interface'
            );
        }

        $data['archiveType']['instance'] = $archive;
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
