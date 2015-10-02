<?php

namespace SQL\Interfaces;

interface ExtendedPDOInterface
{
    public function fetchAffected($statement, array $values = []);

    public function fetchAll($statement, array $values = [], $callback = null);

    public function fetchAssoc($statement, array $values = [], $callback = null);

    public function fetchColumn($statement, array $values = [], $callback = null);

    public function fetchObject($statement, array $values = [], $className = 'stdClass', array $constructorArgs = []);

    public function fetchOne($statement, array $values = []);

    public function fetchValue($statement, array $values = []);

    public function getPdo();

    public function execute($statement, array $values = []);
}

