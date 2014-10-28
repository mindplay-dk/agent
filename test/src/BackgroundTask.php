<?php

/**
 * This class will launch PHP 5.4 and run a script, in the background,
 * and terminate the script after use.
 */
class BackgroundTask
{
    /** @var resource */
    protected $proc;

    /** @var resource[] */
    protected $handles;

    public function __construct($script, $path = null)
    {
        if ($path === null) {
            $path = getcwd();
        }

        $descriptorspec = array(
            0 => array('pipe', 'r'), // stdin
            1 => array('pipe', 'w'), // stdout
            2 => array('pipe', 'a') // stderr
        );

        $cmd = "php {$script}";

        $this->proc = proc_open($cmd, $descriptorspec, $this->handles);
    }

    public function __destruct()
    {
        fclose($this->handles[0]);
        fclose($this->handles[1]);

        if (stripos(php_uname('s'), 'win') >- 1) {
            $status = proc_get_status($this->proc);

            exec("taskkill /F /T /PID {$status['pid']}");
        } else {
            proc_terminate($this->proc);
        }

        proc_close($this->proc);
    }
}
