<?php

namespace Digidirect\AI\Model\Logger\Types\File;

interface BackupInterface
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function backup();
}
