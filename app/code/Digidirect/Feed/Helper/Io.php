<?php

namespace Digidirect\Feed\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Io\File as IoFile;

class Io extends AbstractHelper
{
    /**
     * @var IoFile
     */
    protected $ioFile;

    /**
     * @param Context $context
     * @param IoFile $ioFile
     */
    public function __construct(
        Context $context,
        IoFile $ioFile
    ) {
        $this->ioFile = $ioFile;

        parent::__construct($context);
    }

    /**
     * Write content to file
     *
     * @param string $filename
     * @param string $content
     * @param string $mode
     * @return $this
     * @throws \Exception
     */
    public function write($filename, $content, $mode = 'w')
    {
        $wait = true;
        $fp = fopen($filename, $mode);
        flock($fp, LOCK_EX, $wait);
        fwrite($fp, $content);
        flock($fp, LOCK_UN);
        fclose($fp);

        $this->ioFile->chmod($filename, 0777);

        if (!$this->ioFile->fileExists($filename)) {
            throw new \Exception(sprintf('File %1 not created.', $filename));
        }

        return $this;
    }

    /**
     * Copy file from one place to another
     *
     * @param string $from
     * @param string $to
     * @return $this
     * @throws \Exception
     */
    public function copy($from, $to)
    {
        if (!$this->ioFile->fileExists($from)) {
            throw new \Exception(sprintf('File %1 not exists.', $from));
        }

        $result = $this->ioFile->cp($from, $to);

        if (!$result) {
            throw new \Exception(sprintf('File %1 not copied to %2', $from, $to));
        }

        $this->ioFile->chmod($to, 0777);

        return $this;
    }

    /**
     * Is directory exists?
     *
     * @param string $path
     * @return bool
     */
    public function dirExists($path)
    {
        $result = $this->ioFile->fileExists($path, false);
        if ($result) {
            $result = is_dir($path);
        }

        return $result;
    }

    /**
     * Remove file
     *
     * @param string $file
     * @return bool
     */
    public function unlink($file)
    {
        return $this->ioFile->rm($file);
    }

    /**
     * Create directory (recursive)
     *
     * @param string $dir
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public function mkdir($dir, $mode = 0777, $recursive = true)
    {
        return $this->ioFile->mkdir($dir, $mode, $recursive);
    }

    /**
     * Remove directory
     *
     * @param string $dir
     * @param bool $recursive
     * @return bool
     */
    public function rmdirRecursive($dir, $recursive = true)
    {
        return $this->ioFile->rmdir($dir, $recursive);
    }
}
