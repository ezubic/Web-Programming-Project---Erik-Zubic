<?php
declare(strict_types=1);

abstract class BaseDAO {
    protected PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }
}
