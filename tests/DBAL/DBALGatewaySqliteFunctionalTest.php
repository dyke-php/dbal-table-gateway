<?php

namespace Delta\TableGateway\DBAL;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\DriverManager;

class DBALGatewaySqliteFunctionalTest extends TestCase
{
    private $gateway;
    private $connection;

    public function setUp() : void
    {
        $this->connection = DriverManager::getConnection(['url' => 'sqlite://:memory:']);
        $this->gateway = new DBALGateway($this->connection);
    }

    public function testBasicFetchSql()
    {
        $this->connection->exec(<<<SQL
            CREATE TABLE basic (id INTEGER, text VARCHAR, flag BOOLEAN, amount FLOAT)
        SQL);

        $this->connection->insert('basic', ['id' => 1, 'text' => 'foo', 'flag' => 0, 'amount' => 123.21]);
        $this->connection->insert('basic', ['id' => 2, 'text' => 'bar', 'flag' => 1, 'amount' => 456.5]);

        $objects = $this->gateway->findBySql(Basic::class, 'SELECT * FROM basic');

        $objects = iterator_to_array($objects);

        $this->assertContainsOnly(Basic::class, $objects);
        $this->assertSame(1, $objects[0]->id);
        $this->assertSame('foo', $objects[0]->text);
        $this->assertSame(false, $objects[0]->flag);
        $this->assertSame(123.21, $objects[0]->amount);

        $this->assertSame(2, $objects[1]->id);
        $this->assertSame('bar', $objects[1]->text);
        $this->assertSame(true, $objects[1]->flag);
        $this->assertSame(456.5, $objects[1]->amount);
    }

    public function testDateTypeFetchSql()
    {
        $this->connection->exec(<<<SQL
            CREATE TABLE DateRecord (id INTEGER, created DATETIME)
        SQL);

        $this->connection->insert('DateRecord', ['id' => 1, 'created' => '2019-09-01 10:00:00']);
        $this->connection->insert('DateRecord', ['id' => 2, 'created' => '2019-11-03 12:00:00']);

        $objects = $this->gateway->findBySql(DateRecord::class, 'SELECT * FROM DateRecord');

        $objects = iterator_to_array($objects);

        $this->assertContainsOnly(DateRecord::class, $objects);
        $this->assertSame(1, $objects[0]->id);
    }
}

class Basic
{
    public int $id;
    public string $text;
    public bool $flag;
    public float $amount;
}

class DateRecord
{
    public int $id;
    public \DateTime $created;
}