<?php

namespace SQL;

use PDO;
use SQL\ExtendedPdo;

class ExtendedPdoTest extends \PHPUnit_Framework_TestCase
{
    protected $pdo;

    protected function setUp()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Need "pdo_sqlite" to test in memory.');
        }

        $this->pdo = new ExtendedPdo('sqlite::memory:');

        $this->createTable();
        $this->insertData();
    }

    protected function createTable()
    {
        $scheme = '
            CREATE TABLE testTableCompanies (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `symbol` VARCHAR(5) UNIQUE NOT NULL,
                `name` VARCHAR(50) NOT NULL
            );
        ';

        $this->pdo->exec($scheme);
    }

    protected function insertData()
    {
        $companies = [
            'AAPL' => 'Apple Inc.',
            'GOOGL' => 'Google Inc.',
            'MSFT' => 'Microsoft Corporation',
            'FB' => 'Facebook, Inc.',
            'AMZN' => 'Amazon.com, Inc.',
            'ORCL' => 'Oracle Corporation',
            'INTC' => 'Intel Corporation',
            'IBM' => 'International Business Machines Corporation',
            'CSCO' => 'Cisco Systems, Inc.',
            'VMW' => 'Vmware, Inc.'
        ];

        foreach ($companies as $symbol => $company) {
            $stmt = "
                INSERT INTO testTableCompanies
                    (`symbol`, `name`)
                VALUES
                    ({$symbol}, {$company})
            ";

            $this->pdo->execute($stmt);
        }
    }

    /**
     * @test
     */
    public function shouldCreatePDOInstance()
    {
        $this->assertInstanceOf('PDO', $this->pdo);
        $this->assertInstanceOf('PDO', $this->pdo->getPdo());
    }

    /**
     * @test
     */
    public function shouldGetErrorCode()
    {
        print_r($this->pdo->errorCode());
    }
}
