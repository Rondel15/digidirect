<?php

namespace Digidirect\Feed\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Digidirect\Feed\Helper\Io;

/**
 * Feed Configuration Class
 */
class Config
{
    const STATUS_COMPLETED = 'completed';
    const STATUS_READY = 'ready';
    const STATUS_PROCESSING = 'processing';
    const STATUS_ERROR = 'error';

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Digidirect\Feed\Helper\Io
     */
    protected $io;

    /**
     * Config constructor.
     *
     * @param Filesystem $filesystem
     * @param Io $io
     */
    public function __construct(
        Filesystem $filesystem,
        Io $io
    ) {
        $this->filesystem = $filesystem;
        $this->io = $io;
    }

    /**
     * Remove all folder and files from base feed folder
     *
     * @return bool
     */
    public function clearBasePath()
    {
        $this->io->rmdirRecursive($this->getBasePath());

        return true;
    }

    /**
     * Base path to feed directory (media/feed)
     *
     * @return string
     */
    public function getBasePath()
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'feed';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Path for store temporary files
     *
     * @return string
     */
    public function getTmpPath()
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . 'tmp';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Path to directory with templates
     *
     * @return string
     */
    public function getTemplatePath()
    {
        $path = dirname(dirname(__FILE__)) . '/Setup/data/template';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Path to directory with rules
     *
     * @return string
     */
    public function getRulePath()
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . 'rule';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Check and return maximum allowed script execution time
     *
     * @return int
     */
    public function getMaxAllowedTime()
    {

        $time = intval(ini_get('max_execution_time'));

        if ($time < 1 || $time > 30) {
            $time = 20;
        }

        return $time;
    }

    /**
     * Check and return maximum allowed memory
     *
     * @return int
     */
    public function getMaxAllowedMemory()
    {
        return 220 * 1024 * 1024;
    }
}
