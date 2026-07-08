<?php
namespace Digidirect\Pronto\Cron;

use Digidirect\Pronto\Helper\Sftpwisersender;

class Sendwiserfile
{

    public function __construct(
        Sftpwisersender $helper)
    {
        $this->helper = $helper;
    }

    public function execute()
    {
        exit;
        $this->helper->sendFile();
    }
}
