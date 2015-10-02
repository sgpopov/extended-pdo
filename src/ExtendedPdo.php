<?php

namespace SQL;

use PDO;
use PDOStatement;

class ExtendedPDO extends PDO
{
    protected $pdo;

    protected $options = [];

    protected $attributes = [];

    public function __construct($dsn, $username = null, $password = null, $options = [], $attributes = [])
    {
        if ($dsn instanceof PDO) {
            $this->pdo = $dsn;
        }
        else {
            $this->pdo = new PDO($dsn, $username, $password, $options);

            foreach ($attributes as $attribute => $value) {
                $this->pdo->setAttribute($attribute, $value);
            }
        }
    }

    /**
     * Returns the PDO object.
     *
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Gets the most recent error code.
     *
     * @return mixed
     */
    public function errorCode()
    {
        return $this->pdo->errorCode();
    }

    /**
     * Gets the most recent error info.
     *
     * @return array
     */
    public function errorInfo()
    {
        return $this->pdo->errorInfo();
    }

    /**
     * Prepares an SQL statement for execution.
     *
     * @param string $statement - The SQL statement to prepare for execution.
     * @param array $options
     *
     * @return \PDOStatement
     */
    public function prepare($statement, $options = [])
    {
        $stmt = $this->pdo->prepare($statement, $options);

        return $stmt;
    }


    /**
     * Prepares an SQL statement with bound values.
     *
     * @param string $statement - The SQL statement to prepare for execution.
     * @param array $values - The values to bind to the statement.
     *
     * @return \PDOStatement
     *
     * @throws Exception
     */
    public function prepareWithBindValues($statement, array $values = [])
    {
        if (!$values) {
            return $this->prepare($statement);
        }

        foreach ($values as $key => $value) {
            $this->bindValue($statement, $key, $value);
        }

        return $statement;
    }

    /**
     * Bind a value using the proper PDO::PARAM_* type.
     *
     * @param \PDOStatement $statement - The statement to bind to.
     * @param $key - The placeholder key.
     * @param $value - The value to bind to the statement.
     *
     * @return bool
     * @throws Exception when the value to be bound is not bindable.
     */
    public function bindValue(PDOStatement $statement, $key, $value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT)) {
            return $statement->bindValue($key, $value, PDO::PARAM_INT);
        }

        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return $statement->bindValue($key, $value, PDO::PARAM_BOOL);
        }

        if (is_null($value)) {
            return $statement->bindValue($key, $value, PDO::PARAM_NULL);
        }

        if (!is_scalar($value)) {
            $type = gettype($value);

            throw new Exception(
                "Cannot bind value of type '{$type}' to placeholder '{$key}'"
            );
        }

        return $statement->bindValue($key, $value, PDO::PARAM_STR);
    }

    /**
     * Executes a query with bound values and returns the resulting PDOStatement
     *
     * @param string $statement - The SQL statement to execute.
     * @param array $values - Values to bind to the query.
     *
     * @return \PDOStatement
     */
    public function execute($statement, array $values = [])
    {
        $statement = $this->prepareWithBindValues($statement, $values);
        $statement->execute();

        return $statement;
    }

    /**
     * Execute an SQL statement and return the number of affected rows.
     *
     * @param string $statement - The SQL statement to prepare and execute.
     *
     * @return int - The number of rows affected by the statement.
     */
    public function exec($statement)
    {
        return $this->pdo->exec($statement);
    }

    /**
     * Returns the number of rows affected by the SQL statement.
     *
     * @param string $statement - The SQL statement to prepare and execute.
     * @param array $values - Values to bind to the query.
     *
     * @return int
     */
    public function rowCount($statement, array $values = [])
    {
        $stmt = $this->execute($statement, $values);

        return $stmt->rowCount();
    }

    public function fetchAll($statement, array $values = [], $callback = null)
    {
        $fetchType = PDO::FETCH_ASSOC;

        return $this->fetchWithCallback($fetchType, $statement, $values, $callback);
    }

    public function fetchAssoc($statement, array $values = [], $callback = null)
    {
        $stmt = $this->execute($statement, $values);

        if ($callback === null) {
            $callback = function ($row) {
                return $row;
            };
        }

        $data = [];

        while ($row = $stmt->fetch(self::FETCH_ASSOC)) {
            $key = current($row);

            $data[$key] = call_user_func($callback, $row);
        }

        return $data;
    }

    public function fetchColumn($statement, array $values = [])
    {

    }

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $statement - The SQL statement to prepare and execute.
     * @param array $values - Values to bind to the query.
     * @param string $className - The name of the class to create from each
     * row.
     * @param array $constructorArgs - Arguments to pass to the
     * object constructor.
     *
     * @return bool|array
     */
    public function fetchObject($statement, array $values = [], $className = 'stdClass', array $constructorArgs = [])
    {
        $stmt = $this->execute($statement, $values);

        if ($constructorArgs) {
            return $stmt->fetchObject($className, $constructorArgs);
        }

        return $stmt->fetchObject($className);
    }

    /**
     * @param int $fetchType
     * @param string $statement - The SQL statement to prepare and execute.
     * @param array $values - Values to bind to the query.
     * @param $callback - A callable to be applied to each of the rows
     * to be returned.
     *
     * @return array
     */
    protected function fetchWithCallback($fetchType, $statement, array $values = [], $callback)
    {
        $stmt = $this->execute($statement, $values);

        if ($fetchType === PDO::FETCH_COLUMN) {
            $data = $stmt->fetchAll($fetchType, 0);
        }
        else {
            $data = $stmt->fetchAll($fetchType);
        }

        return $this->applyCallback($callback, $data);
    }

    /**
     * Applies a callback to the data.
     *
     * @param $callback - The callback to apply.
     * @param array $data - The data.
     *
     * @return array
     */
    protected function applyCallback($callback, $data)
    {
        if ($callback !== null) {
            foreach ($data as $key => $value) {
                $data[$key] = call_user_func($callback, $value);
            }
        }

        return $data;
    }

    /**
     * Returns the last inserted autoincrement sequence value.
     *
     * @param string $name - Name of the sequence object from which
     * the ID should be returned.
     *
     * @return int
     */
    public function lastInsertId($name = null)
    {
        $id = $this->pdo->lastInsertId($name);

        return $id;
    }
}
