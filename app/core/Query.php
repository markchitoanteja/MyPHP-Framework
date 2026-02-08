<?php

require_once __DIR__ . '/Database.php';

final class Query
{
    /**
     * Start a fluent query for a table.
     *
     * @param string $table Table name.
     */
    public static function table(string $table): QueryBuilder
    {
        return new QueryBuilder(DB::pdo(), $table);
    }
}

final class QueryBuilder
{
    private PDO $pdo;

    private string $table;
    private array $select = ['*'];
    private array $wheres = [];
    private array $params = [];
    private ?string $orderBy = null;
    private string $orderDir = 'ASC';
    private ?int $limit = null;
    private ?int $offset = null;

    /**
     * @param PDO $pdo Active PDO connection.
     * @param string $table Table name.
     */
    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $this->id($table);
    }

    /**
     * Set selected columns.
     *
     * Examples:
     *  - select('*')
     *  - select('id, name, email')
     *
     * @param string $columns Comma-separated columns or '*'.
     */
    public function select(string $columns = '*'): self
    {
        $columns = trim($columns);

        if ($columns === '*') {
            $this->select = ['*'];
            return $this;
        }

        $parts = array_map('trim', explode(',', $columns));
        $this->select = array_map(fn($c) => $this->id($c), $parts);

        return $this;
    }

    /**
     * Add WHERE conditions.
     *
     * Supported forms:
     *  - where('id', 3)
     *  - where('name', 'Mark')
     *  - where('id', '>=', 3)
     *  - where(['id' => 3, 'name' => 'Mark'])
     *  - where('id', 'IN', [1,2,3])
     *
     * @param string|array $field Column name or associative array of column => value.
     * @param mixed $opOrValue Operator (when using 3 args) or value (when using 2 args).
     * @param mixed|null $value Value (when using 3 args).
     *
     * @throws InvalidArgumentException For invalid operators or IN with invalid values.
     */
    public function where(string|array $field, mixed $opOrValue = null, mixed $value = null): self
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $this->where($k, '=', $v);
            }
            return $this;
        }

        if ($value === null) {
            $op = '=';
            $val = $opOrValue;
        } else {
            $op = (string)$opOrValue;
            $val = $value;
        }

        $op = strtoupper(trim($op));
        $allowedOps = ['=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'];

        if (!in_array($op, $allowedOps, true)) {
            throw new InvalidArgumentException("Invalid operator: {$op}");
        }

        $col = $this->id($field);

        if ($op === 'IN' || $op === 'NOT IN') {
            if (!is_array($val) || count($val) === 0) {
                throw new InvalidArgumentException("{$op} requires a non-empty array.");
            }

            $placeholders = [];
            foreach ($val as $v) {
                $placeholders[] = $this->param($v);
            }

            $this->wheres[] = "{$col} {$op} (" . implode(', ', $placeholders) . ")";
            return $this;
        }

        $ph = $this->param($val);
        $this->wheres[] = "{$col} {$op} {$ph}";

        return $this;
    }

    /**
     * Set ORDER BY clause.
     *
     * @param string $column Column name.
     * @param string $dir Sort direction ('ASC' or 'DESC'). Defaults to 'ASC'.
     *
     * @throws InvalidArgumentException For invalid column identifiers.
     */
    public function orderBy(string $column, string $dir = 'ASC'): self
    {
        $this->orderBy = $this->id($column);

        $dir = strtoupper(trim($dir));
        $this->orderDir = ($dir === 'DESC') ? 'DESC' : 'ASC';

        return $this;
    }

    /**
     * Set LIMIT and optional OFFSET.
     *
     * @param int $limit Maximum rows to return.
     * @param int $offset Rows to skip before returning results.
     */
    public function limit(int $limit, int $offset = 0): self
    {
        $this->limit = max(0, $limit);
        $this->offset = max(0, $offset);
        return $this;
    }

    /**
     * Execute the built SELECT query and return all rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        $sql = $this->buildSelectSql();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute the built SELECT query and return the first row (or null).
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $prevLimit = $this->limit;
        $prevOffset = $this->offset;

        if ($this->limit === null) {
            $this->limit(1, 0);
        }

        $rows = $this->get();

        $this->limit = $prevLimit;
        $this->offset = $prevOffset;

        return $rows[0] ?? null;
    }

    /**
     * Insert a row into the current table.
     *
     * @param array<string, mixed> $data Column => value.
     * @return string Last insert ID.
     */
    public function insert(array $data): string
    {
        if (!$data) {
            throw new InvalidArgumentException("Insert data cannot be empty.");
        }

        $cols = [];
        $vals = [];

        foreach ($data as $k => $v) {
            $cols[] = $this->id((string)$k);
            $vals[] = $this->param($v);
        }

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $cols) . ")
                VALUES (" . implode(', ', $vals) . ")";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);

        return $this->pdo->lastInsertId();
    }

    /**
     * Update rows in the current table.
     *
     * Requires at least one WHERE condition for safety.
     *
     * @param array<string, mixed> $data Column => value.
     * @return int Affected rows.
     */
    public function update(array $data): int
    {
        if (!$data) {
            throw new InvalidArgumentException("Update data cannot be empty.");
        }
        if (!$this->wheres) {
            throw new RuntimeException("Refusing to UPDATE without WHERE.");
        }

        $sets = [];
        foreach ($data as $k => $v) {
            $col = $this->id((string)$k);
            $ph = $this->param($v);
            $sets[] = "{$col} = {$ph}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . $this->buildWhereSql();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);

        return $stmt->rowCount();
    }

    /**
     * Delete rows from the current table.
     *
     * Requires at least one WHERE condition for safety.
     *
     * @return int Affected rows.
     */
    public function delete(): int
    {
        if (!$this->wheres) {
            throw new RuntimeException("Refusing to DELETE without WHERE.");
        }

        $sql = "DELETE FROM {$this->table}" . $this->buildWhereSql();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);

        return $stmt->rowCount();
    }

    /**
     * Build the final SELECT SQL statement.
     */
    private function buildSelectSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->select) . " FROM {$this->table}";
        $sql .= $this->buildWhereSql();

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy} {$this->orderDir}";
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . (int)$this->limit;

            if ($this->offset !== null && $this->offset > 0) {
                $sql .= " OFFSET " . (int)$this->offset;
            }
        }

        return $sql;
    }

    /**
     * Build the WHERE SQL fragment.
     */
    private function buildWhereSql(): string
    {
        if (!$this->wheres) return '';
        return " WHERE " . implode(' AND ', $this->wheres);
    }

    /**
     * Register a bound parameter and return its placeholder.
     *
     * @param mixed $value Value to bind.
     */
    private function param(mixed $value): string
    {
        $key = ':p' . (count($this->params) + 1);
        $this->params[$key] = $value;
        return $key;
    }

    /**
     * Validate and return a safe SQL identifier (table/column).
     *
     * Allows: letters, numbers, underscore. Also allows '*' for SELECT.
     *
     * @throws InvalidArgumentException For invalid identifiers.
     */
    private function id(string $identifier): string
    {
        $identifier = trim($identifier);

        if ($identifier === '*') {
            return '*';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            throw new InvalidArgumentException("Invalid identifier: {$identifier}");
        }

        return $identifier;
    }
}
