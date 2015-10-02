<?php

namespace SQL\Interfaces;

interface LoggerInterface
{
    public function setActive($active);

    public function isActive();

    public function addLog($duration, $function, $statement, array $bindValues);

    public function getLog();

    public function resetLog();
}
