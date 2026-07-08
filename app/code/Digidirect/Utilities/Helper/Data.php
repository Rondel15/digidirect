<?php
namespace Digidirect\Utilities\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Json\DecoderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Variable\Model\VariableFactory;
use Magento\Variable\Model\Variable;

class Data extends AbstractHelper
{
    const DIGIDIRECT_EXTENSION_PREFIX = 'Digidirect_';

    /**
     * DeploymentConfig
     *
     * @var DeploymentConfig
     */
    protected $_deploymentConfig;

    /**
     * @var \Magento\Framework\Module\Dir
     */
    protected $_moduleDir;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_fileDriver;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var []
     */
    protected $_customVariables;

    /**
     * @var VariableFactory
     */
    protected $_customVariableFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Variable
     */
    protected $_customVariableModel = null;

    /**
     * Data Constructor
     *
     * @param Context $context
     * @param DeploymentConfig $deploymentConfig
     * @param Dir $moduleDir
     * @param File $fileDriver
     * @param DecoderInterface $jsonDecoder
     * @param ModuleListInterface $moduleList
     * @param VariableFactory $variableFactory
     */
    public function __construct(
        Context $context,
        DeploymentConfig $deploymentConfig,
        Dir $moduleDir,
        File $fileDriver,
        DecoderInterface $jsonDecoder,
        ModuleListInterface $moduleList,
        VariableFactory $variableFactory
    ) {
        $this->_deploymentConfig = $deploymentConfig;
        $this->_moduleDir = $moduleDir;
        $this->_fileDriver = $fileDriver;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_moduleList = $moduleList;
        $this->_customVariableFactory = $variableFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * Get current deployment mode (developer | production | default)
     *
     * @return string
     */
    public function getDeploymentMode()
    {
        return $this->_deploymentConfig->get(State::PARAM_MODE);
    }

    /**
     * Return test selector
     *
     * @param string $str
     * @return string
     */
    public function getTestSelector($str)
    {
        $deployMode = $this->getDeploymentMode();
        return $deployMode == State::MODE_DEVELOPER ? 'data-test="' . $str . '"' : '';
    }

    /**
     * @param string $moduleName
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getModuleVersion($moduleName)
    {
        $composerJson = realpath($this->_moduleDir->getDir($moduleName) . '/composer.json');
        if ($composerJson && $this->_fileDriver->isExists($composerJson)) {
            $json = $this->_jsonDecoder->decode($this->_fileDriver->fileGetContents($composerJson));
            if (isset($json['version'])) {
                return $json['version'];
            }
        }
        return false;
    }

    /**
     * Get list of enabled extensions by vendor name
     *
     * @param string|null $vendor
     * @return array
     */
    public function getEnabledExtensions($vendor = self::DIGIDIRECT_EXTENSION_PREFIX)
    {
        $result = [];
        $extensions = array_keys($this->_moduleList->getAll());
        foreach ($extensions as $extension) {
            $disableOutputPath = 'advanced/modules_disable_output/' . $extension;
            if ((!$vendor || substr($extension, 0, strlen($vendor)) == $vendor)
                && !$this->_scopeConfig->isSetFlag($disableOutputPath, ScopeInterface::SCOPE_STORES)
            ) {
                $result[] = $extension;
            }
        }
        return $result;
    }

    /**
     * @param string $variableCode
     * @return \Magento\Variable\Model\Variable
     */
    public function getCustomVariable($variableCode)
    {
        if (!isset($this->_customVariables[$variableCode])) {
            if (null === $this->_customVariableModel) {
                $this->_customVariableModel = $this->_customVariableFactory->create();
            }
            $variable = $this->_customVariableModel->loadByCode($variableCode);
            $this->_customVariables[$variableCode] =  $variable;
        }

        return $this->_customVariables[$variableCode];
    }

    /**
     * Get config value
     * @param string $xmlPath
     * @return mixed
     */
    public function getConfig($xmlPath)
    {
        return $this->_scopeConfig->getValue($xmlPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Replace symbols which have utf8mb4 encoding
     *
     * @param string $string
     * @param string $replacement
     * @return null|string
     */
    public function replace4byte($string, $replacement = '')
    {
        $pattern = '%(?:\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})%xs';

        return preg_replace($pattern, $replacement, $string);
    }
}
