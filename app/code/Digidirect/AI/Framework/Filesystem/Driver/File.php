<?php
namespace Digidirect\AI\Framework\Filesystem\Driver;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Class File
 * @package Magento\Framework\Filesystem\Driver
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class File extends \Magento\Framework\Filesystem\Driver\File implements DriverInterface
{
    const CHARSET_MAIN = 'UTF-8';
    const CHARSET_ALLOWED = 'cp1251';

    /**
     * Convert non utf8 symbols
     *
     * {@inheritdoc}
     */
    public function fileGetCsv($resource, $length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $result = parent::fileGetCsv($resource, $length, $delimiter, $enclosure, $escape);
        if (is_array($result)) {
            $result = array_map([$this, 'convertString'], $result);
        }

        return $result;
    }

    /**
     * Convert tString
     *
     * @param string $content
     * @return string
     * @throws \Exception
     */
    public function convertString($content)
    {
        if (!mb_check_encoding($content, self::CHARSET_MAIN)) {
            if (mb_check_encoding($content, self::CHARSET_ALLOWED)) {
                $content = mb_convert_encoding($content, self::CHARSET_MAIN, self::CHARSET_ALLOWED);
            }
        }

        return $content;
    }
}
