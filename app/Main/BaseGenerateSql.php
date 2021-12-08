<?php

namespace App\Main;

class BaseGenerateSql
{
    protected array $sql;
    protected array $tables;
    private int $depth;
    private static int $count = 0;

    public function __construct(array $tables, int $depth)
    {
        $this->sql = [];
        $this->tables = $tables;
        $this->depth = $depth;
    }

    public function apply()
    {
        foreach ($this->tables as $table => $value) {
            if (method_exists($this, $table)) {
                $value = $this->{$table}($value);
                if ($value) {
                    $this->sql[] = $value;
                }
            }
        }

        return $this->sql ? join(PHP_EOL, $this->sql) : '';
    }

    protected function tree($entity)
    {
        static $depth = 0;

        $id = $entity->id;
        $entity_path = $entity->path;

        unset($entity->id);
        unset($entity->path);

        $sql_create = $this->createFieldValue($entity);

        if (count($sql_create) === 0) {
            return '';
        }

        $sql = PHP_EOL . PHP_EOL;
        $sql .= '--' . PHP_EOL;
        $sql .= sprintf('-- DEPTH: %s | ID: %s PATH: %s', $this->depth, $id, $entity_path) . PHP_EOL;
        $sql .= '-- ' . PHP_EOL . PHP_EOL;

        if ($depth < $this->depth) {
            $sql .= "SET @path_depth_{$this->depth} = @path_to_tree;" . PHP_EOL;
            $sql .= "SET @last_tree_depth_{$this->depth} = @last_tree_depth_" . ($this->depth - 1) . ";" . PHP_EOL;
        }

        if ($depth !== $this->depth) {
            $sql .= "SET @path_to_tree = REPLACE( CONCAT( @path_depth_{$this->depth}, '.', @last_tree_depth_{$this->depth} ), '..', '.' );" . PHP_EOL . PHP_EOL;
        }
        $count = self::$count++;
        $sql .= "SET @track_no = {$count};##$count" . PHP_EOL . PHP_EOL;
        $sql .= "INSERT INTO tree SET `path` = @path_to_tree, " . join(', ', $sql_create) . ";" . PHP_EOL . PHP_EOL;
        $sql .= "SET @last_tree_depth_{$this->depth} = LAST_INSERT_ID();" . PHP_EOL;

        $sql .= "INSERT INTO migrate_data SET `entity`='tree', `old_id`='" . $id . "', `new_id`= LAST_INSERT_ID(), `date_added`=@date_added;" . PHP_EOL;

        $depth = $this->depth;
        return $sql;
    }

    protected function tree_data($entity)
    {
        if (!$entity) return '';
        unset($entity->element_id);

        $sql = $this->createFieldValue($entity);

        if (count($sql) === 0) {
            return '';
        }

        $sql_str = "";
        $count = self::$count++;
        $sql_str .= PHP_EOL . "SET @track_no = {$count};##$count" . PHP_EOL . PHP_EOL;
        $sql_str .= "REPLACE INTO tree_data SET `element_id` = @last_tree_depth_{$this->depth}, " . join(', ', $sql) . ";";
        return $sql_str;
    }

    protected function devices($entities)
    {
        if (!$entities) return '';

        $sql_str = "";

        foreach ($entities as $entity) {
            $id = $entity->id;
            unset($entity->id);

            $sql = $this->createFieldValue($entity);

            if (count($sql) === 0) {
                continue;
            }
            $count = self::$count++;
            $sql_str .= PHP_EOL . "SET @track_no = {$count};##$count" . PHP_EOL . PHP_EOL;
            $sql_str .= "INSERT INTO devices SET " . join(', ', $sql) . ";" . PHP_EOL;
            $sql_str .= "INSERT INTO migrate_data SET `entity`='devices', `old_id`='" . $id . "', `new_id`=LAST_INSERT_ID(), `date_added`=@date_added;" . PHP_EOL;
        }

        return $sql_str;
    }

    protected function registrators($entities)
    {
        if (!$entities) return '';

        $sql_str = "";

        foreach ($entities as $entity) {
            $id = $entity->id;
            unset($entity->id);

            $sql = $this->createFieldValue($entity);

            if (count($sql) === 0) {
                continue;
            }
            $count = self::$count++;
            $sql_str .= PHP_EOL . "SET @track_no = {$count};##$count" . PHP_EOL . PHP_EOL;
            $sql_str .= "INSERT INTO registrators SET " . join(', ', $sql) . ";" . PHP_EOL;
            $sql_str .= "INSERT INTO migrate_data SET `entity`='registrators', `old_id`='" . $id . "', `new_id`=LAST_INSERT_ID(), `date_added`=@date_added;" . PHP_EOL;
        }

        return $sql_str;
    }

    protected function createFieldValue($entity): array
    {
        $sql = [];

        foreach ($entity as $field => $value) {
            if (is_null($value)) {
                $value = 'null';
            } else if (is_string($value)) {
                $value = "'" . $value . "'";
            };

            $sql[] = sprintf('`%s` = %s', $field, $value);
        }

        return $sql;
    }
}
