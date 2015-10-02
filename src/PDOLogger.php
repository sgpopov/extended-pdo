<?php

namespace SQL;

use SQL\Interfaces\LoggerInterface;

class PDOLogger implements LoggerInterface
{
    protected $active = false;

    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

    public function isActive()
    {
        return (bool) $this->active;
    }

    public function addLog($duration, $function, $statement, array $bindValues)
    {
        if ($this->active === false) {
            return;
        }
    }

    public function getLog()
    {

    }

    public function resetLog()
    {

    }
}
