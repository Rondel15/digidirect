<?php
namespace Digidirect\AI\Model\Lib\Adapter\Import;

use Magento\Framework\Simplexml\Element as SimplexmlEl;
use Magento\Framework\Simplexml\ConfigFactory as SimplexmlFactory;
use Magento\Framework\Simplexml\Config as Simplexml;
use Digidirect\AI\Model\Engine\Processor\Exception\ProcessException;

/**
 * Class Xml
 *
 * @package Digidirect\AI\Model\Lib\Adapter\Import
 */
class Xml
{
    /**
     * SimplexmlFactory
     *
     * @var SimplexmlFactory
     */
    protected $_simplexmlFactory;

    /**
     * Flag if reading start
     *
     * @var bool
     */
    protected $started = false;

    /**
     * XMLReader
     *
     * @var \XMLReader
     */
    protected $reader;

    /**
     * Sys Reader
     *
     * @var \XMLReader
     */
    private $_sysReader;

    /**
     * Node name
     *
     * @var string
     */
    protected $_nodeName;

    /**
     * Batch Count
     *
     * @var int
     */
    protected $_batchCount;

    /**
     * Xml constructor.
     *
     * @param SimplexmlFactory $simplexmlFactory
     */
    public function __construct(SimplexmlFactory $simplexmlFactory)
    {
        $this->_simplexmlFactory = $simplexmlFactory;
        $this->reader = new \XMLReader();
        $this->_sysReader = new \XMLReader();
    }

    /**
     * Open
     *
     * @param string $file
     * @throws ProcessException
     * @return void
     */
    public function open($file)
    {
        if (empty($file) || !is_file($file)) {
            throw new ProcessException('Please upload file');
        }
        if (!$this->reader->open($file)) {
            throw new ProcessException('Can not open file ' . $file);
        }
    }

    /**
     * Validate Xml
     *
     * @param string $xmlPart
     * @throws ProcessException
     * @return void
     */
    protected function validateXml($xmlPart)
    {
        if (empty($xmlPart) || !$this->_sysReader->XML($xmlPart, null, LIBXML_DTDVALID)) {
            throw new ProcessException('Xml Structure is not valid. File Can Not be Parsed.');
        }
    }

    /**
     * @param string $file
     * @param bool $asString
     * @return Simplexml|string
     * @throws ProcessException
     */
    public function getUnprocessedData($file, $asString = true)
    {
        $first = false;
        if (!$this->started) {
            $this->open($file);
            $this->getFirstNode();
            $this->started = true;
            $first = true;
        }

        $count = 1;
        if ($this->_batchCount) {
            $count = $this->_batchCount;
        }

        $xml = '';
        $i = 0;
        while ($i < $count) {
            if (!$first) {
                if (!$this->reader->next($this->_nodeName)) {
                    break;
                }
            }
            $xmlPart = $this->reader->readOuterXml();
            if ($first && !$xmlPart) {
                return '';
            }
            $this->validateXml($xmlPart);
            $xml .= $xmlPart;

            $first = false;
            $i++;
        }

        if ($asString) {
            return $xml;
        }

        return $this->_simplexmlFactory->create(['sourceData' => $xml]);
    }

    /**
     * Set node name
     *
     * @param string $name
     * @return $this
     */
    public function setNodeName($name)
    {
        $this->_nodeName = $name;

        return $this;
    }

    /**
     * Set batch count.
     * Used to get parts of xml from few nodes.
     *
     * @param int $batchCount
     * @return $this
     */
    public function setBatchCount($batchCount)
    {
        $this->_batchCount = $batchCount;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBatchCount()
    {
        return $this->_batchCount;
    }

    /**
     * Allow to process new file
     *
     * @return void
     */
    public function startNew()
    {
        $this->started = false;
    }

    /**
     * Get first node
     *
     * @return void
     */
    private function getFirstNode()
    {
        while ($this->reader->read() && $this->reader->name !== $this->_nodeName) {
            true;
        }
    }

    /**
     * Make array from Xml
     *
     * @param SimplexmlEl $node
     * @return array|string
     */
    public static function xmlToAssoc(SimplexmlEl $node)
    {
        $data = [];
        // add children values
        if ($node->hasChildren()) {
            foreach ($node->children() as $childName => $child) {
                $entry = self::xmlToAssoc($child);
                if ($node->getAttribute('Type') == 'Array') {
                    $data[$childName][] = $entry;
                } else {
                    $data[$childName] = $entry;
                }
            }
        } else {
            return (string)$node;
        }

        return $data;
    }

    /**
     * Make Xml from array
     *
     * @param [] $data
     * @param string|null $root
     * @param [] $namespaces
     * @return string
     */
    public static function assocToXml($data, $root = null, $namespaces = [])
    {
        $xml = new SimplexmlEl(!empty($root) ? '<' . $root . '/>' : '<root/>');

        // Register namespace prefixes
        foreach ($namespaces as $prefix => $ns) {
            if ($prefix != '') {
                $xml->registerXPathNamespace($prefix, $ns);
            }
        }

        return self::_assocToXml($data, $xml)->asNiceXml();
    }

    /**
     * Assoc to xml
     *
     * @param [] $data
     * @param SimplexmlEl $xml
     * @return SimplexmlEl
     */
    private static function _assocToXml($data, SimplexmlEl $xml)
    {
        //var_dump($data);
        foreach ($data as $key => $value) {
            if ($key == '@' || is_numeric($key)) {
                continue;
            }

            if (!is_array($value)) {
                $xml->addChild($key, $xml->xmlentities($value));
            } elseif (array_unique(array_map("is_int", array_keys($value))) === [true]) {
                //for int array need create array in xml
                $xml->addAttribute('Type', 'Array');
                foreach ($value as $v) {
                    $childXml = self::_assocToXml($v, new SimplexmlEl('<' . $key . '/>'));
                    $xml->appendChild($childXml);
                }
            } else {
                $xml->addChild($key);
                self::_assocToXml($value, $xml->{$key});
            }
        }

        return $xml;
    }
}
