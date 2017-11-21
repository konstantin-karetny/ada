<?php
    /**
    * @package    package_lib
    * @version    1.0.0 11.10.2016
    * @copyright  copyright
    * @license    Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Lib;

    use PDO;
    use PDOException;

    class Db extends Singleton {

        private
            $type        = 'mysql',
            $host        = 'localhost',
            $name        = '',
            $prefix      = '',
            $charset     = 'utf8',
            $user        = '',
            $password    = '',
            $pdo         = null,
            $pdo_options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
           ],
            $stmt         = null,
            $quote_name   = '`',
            $query_params = [];

        public function __construct(array $options) {
            foreach ([
                'type',
                'host',
                'name',
                'prefix',
                'charset',
                'user',
                'password',
                'pdo_options'
           ] as $key) {
                if (key_exists($key, $options)) {
                    if (is_array($this->$key)) {
                        $this->$key = $options[$key] + $this->$key;
                    }
                    else {
                        $this->$key = $options[$key];
                    }
                }
            }
            $this->pdo = $this->getPdo();
        }

        private function getPdo() {
            switch ($this->type) {
                case 'mysql':
                    $dsn = $this->type . ':' . 'host=' . $this->host . ';dbname=' . $this->name . ';charset=' . $this->charset;
                    break;
            }
            try {
                $pdo = new PDO($dsn, $this->user, $this->password, $this->pdo_options);
            } catch (PDOException $e) {
                throw new \Exception('Unable to connect to database. ' . $e->getMessage());
            }
            return $pdo;
        }

        public function setAttribute(int $attribute, $value) {
            return $this->pdo->setAttribute($attribute, $value);
        }

        public function esc($value) {
            if (is_numeric($value)) {
                $value *= 1;
                if (is_int($value) || is_float($value)) {
                    return $value;
                }
            }

            $this->query_params[] = $value;
            end($this->query_params);
            return ':_' . key($this->query_params);
        }

        public function qn(string $names, string $aliases = '', bool $dot_notation = true) {
            $res     = '';
            $qn      = $this->quote_name;
            $aliases = explode(',', $aliases);
            foreach (explode(',', $names) as $i => $name) {
                if (!$name = trim($name) ) continue;

                $name = str_replace($qn, ($qn . $qn), $name);
                if ($dot_notation) {
                    $name = str_replace('.', ($qn . '.' . $qn), $name);
                }
                $res .= $qn . $name . $qn;
                if (
                    isset($aliases[$i]) &&
                    ( $alias = trim($aliases[$i]) )
               ) {
                    $alias = str_replace($qn, ($qn . $qn), $alias);
                    $res  .= ' AS ' . $qn . $alias . $qn;
                }
                $res .= ', ';
            }
            return rtrim($res, ', ');
        }

        public function sqlIn(array $array) {
            $array = array_map(function($el) { return $this->esc($el); }, $array);
            return 'IN(' . implode(', ', $array) . ')';
        }

        public function sqlSet(array $params) {
            $pairs = [];
            foreach ($params as $key => $val) {
                $pairs[] = $this->qn($key) . ' = ' . $this->esc($val);
            }
            return 'SET ' . implode(', ', $pairs);
        }

        private function prepare(string $query) {
            $query      = trim($query);
            $query      = str_replace('#__', $this->prefix, $query);
            $query      = str_replace(PHP_EOL, ' ', $query);
            $query      = preg_replace('#\s+#', ' ', $query);
            $this->stmt = $this->pdo->prepare($query);
            return $query;
        }

        public function execute(string $query) {
            $query = $this->prepare($query);
            preg_match_all("#:_([0-9]+)#", $query, $matches);
            $params_indexes = $matches[1];
            if (!$params_indexes) {
                return $this->stmt->execute();
            }

            $res = true;
            for ($i = 0, $limit = count($params_indexes); $i < $limit; $i++) {
                foreach ($params_indexes as $index) {
                    if (!isset($this->query_params[$index])) {
                        throw new Except("Query string must not contain ':' symbol. Query '$query'");
                    }

                    $param = $this->query_params[$index];
                    if (is_array($param)) {
                        if (!isset($param[$i])) break 2;

                        $param = $param[$i];
                    }
                    $this->stmt->bindValue(':_' . $index, $param);
                }
                if (!$this->stmt->execute()) {
                    $res = false;
                }
            }
            foreach ($params_indexes as $index) {
                unset($this->query_params[$index]);
            }
            return $res;
        }

        public function loadField(string $query, $default = '') {
            $this->execute($query);
            $array = $this->stmt->fetch(PDO::FETCH_NUM);
            return isset($array[0]) ? $array[0] : $default;
        }

        public function loadColumn(string $query, string $column, string $key = '', $default = []) {
            $this->execute($query);
            $col = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$col) return $default;

            if ($key !== '' && !key_exists($key, $col[0])) {
                throw new Except("Unknown key '$key' among columns '" . implode(', ', array_keys($col[0])) . "'. Query '$query'");
            }

            if (!isset($col[0][$column])) {
                throw new Except("Unknown column '$column' among columns '" . implode(', ', array_keys($col[0])) . "'. Query '$query'");
            }

            $res      = [];
            $is_keyed = $key !== '';
            foreach ($col as $el) {
                if ($is_keyed) {
                    $res[$el[$key]] = $el[$column];
                }
                else {
                    $res[] = $el[$column];
                }
            }
            return $res;
        }

        public function loadArray(string $query, string $type = 'ASSOC', $default = null) {
            $this->execute($query);
            $fetch_style = ( strtoupper($type) == 'ASSOC' ) ? PDO::FETCH_ASSOC : PDO::FETCH_NUM;
            $array       = $this->stmt->fetch($fetch_style);
            return $array ? $array : $default;
        }

        public function loadArrays(string $query, string $key = '', string $type = 'ASSOC', $default = []) {
            $this->execute($query);
            $fetch_style = (strtoupper($type) == 'ASSOC') ? PDO::FETCH_ASSOC : PDO::FETCH_NUM;
            $arrays = $this->stmt->fetchAll($fetch_style);
            if (!$arrays) return $default;

            if ($key !== '' && !key_exists($key, $arrays[0])) {
                throw new Except("Unknown key '$key' among columns '" . implode(', ', array_keys($arrays[0])) . "'. Query '$query'");
            }

            if ($key === '') return $arrays;

            $arrays_keyed = [];
            foreach ($arrays as $arr) {
                $arrays_keyed[$arr[$key]] = $arr;
            }
            return $arrays_keyed;
        }

        public function loadObject(string $query, $default = null) {
            $this->execute($query);
            $obj = $this->stmt->fetch();
            return $obj ? $obj : $default;
        }

        public function loadObjects(string $query, string $key = '', $default = []) {
            $this->execute($query);
            $objects = $this->stmt->fetchAll();
            if (!$objects) return $default;

            if ($key !== '' && !property_exists($objects[0], $key)) {
                throw new Except("Unknown key '$key' among columns '" . implode(', ', array_keys( (array) $objects[0] )) . "'. Query '$query'");
            }

            if ($key === '') return $objects;

            $objects_keyed = [];
            foreach ($objects as $obj) {
                $objects_keyed[$obj->$key] = $obj;
            }
            return $objects_keyed;
        }

        public function insert(string $table_name, array $data) {
            if (!is_array(reset($data))) {
                $data = [$data];
            }
            $this->execute('INSERT INTO ' . $this->qn('#__' . $table_name) . ' ' . $this->sqlSet(ArrayHelper::groupByKeys($data)));
            return $this->lastInsertId();
        }

        public function update(string $table_name, array $data, string $condition) {
            if (!is_array(reset($data))) {
                $data = [$data];
            }
            return $this->execute('UPDATE ' . $this->qn('#__' . $table_name) . ' ' . $this->sqlSet(ArrayHelper::groupByKeys($data)) . ' ' . $condition);
        }

        public function beginTransaction() {
            return $this->pdo->beginTransaction();
        }

        public function inTransaction() {
            return $this->pdo->inTransaction();
        }

        public function commit() {
            return $this->pdo->commit();
        }

        public function rollBack() {
            return $this->pdo->rollBack();
        }

        public function lastInsertId() {
            return $this->pdo->lastInsertId();
        }

        public function errorCode() {
            return $this->pdo->errorCode();
        }

        public function errorInfo() {
            return $this->pdo->errorInfo();
        }

        public static function getAvailableDrivers() {
            return PDO::getAvailableDrivers();
        }

        public function getAttribute(int $attribute) {
            return $this->pdo->getAttribute($attribute);
        }

        public function getType() {
            return $this->type;
        }

        public function getHost() {
            return $this->host;
        }

        public function getName() {
            return $this->name;
        }

        public function getPrefix() {
            return $this->prefix;
        }

        public function getCharset() {
            return $this->charset;
        }

        public function getQuoteName() {
            return $this->quote_name;
        }

        public function close() {
            $this->pdo          = null;
            $this->query_params = [];
        }

        /** @return Db */ public static function getInst($params = []) { return parent::getInst($params); }

    }
