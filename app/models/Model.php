<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Base mínima de los modelos.
 *
 * Centraliza el acceso a PDO y tres operaciones repetidas: consultar
 * varias filas, una fila o un valor escalar. Todas usan sentencias
 * preparadas con parámetros vinculados.
 */
abstract class Model
{
    protected PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /**
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @param array<string, mixed> $params
     * @return mixed
     */
    protected function scalar(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();

        return $value === false ? null : $value;
    }
}
