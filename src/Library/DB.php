<?php

namespace App\Library;

use Exception;

final class DB
{
    /** Аккредитивы доступа к базе данных*/
    protected $credentials;
   /** Ресурс соединения с базой данных (дескриптор) */
    protected $conn;
    /** Результат последнего запроса */
    protected $last_result;
    /** Логировать запросы к БД */
    protected $logEnabled = false;  // Протоколирование выполняемых SQL-запросов (вкл/выкл)

    public function __construct($db_conn_str, $auto_connect = true)
    {
        $this->credentials = $db_conn_str;

        $valid_db_types = ['pgsql'];
        if (empty($this->credentials['driver']) or !in_array($this->credentials['driver'], $valid_db_types)) {
            throw new Exception('{075D076C-4E82-4900-8E02-8C732FB9477C}');
        }

        if (empty($this->credentials['host'])) {
            $this->credentials['host'] = 'localhost';
        }

        if (empty($this->credentials['port'])) {
            $this->credentials['port'] = '5433';
        }

        if (empty($this->credentials['username'])) {
            $this->credentials['username'] = '';
        }

        if (empty($this->credentials['password'])) {
            $this->credentials['password'] = '';
        }

        if (empty($this->credentials['database'])) {
            $this->credentials['database'] = '';
        }

        if ($auto_connect) {
            self::connect();
        }
    }

    public function getConnect(){
        return $this->conn;
    }

    // Подключение к СУБД
    private function connect()
    {
        if (empty($this->credentials)) {
            throw new Exception('Неверные параметры соединения с БД');
        }

        try {
            $conStr = sprintf(
                "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
                $this->credentials['host'],
                $this->credentials['port'],
                $this->credentials['database'],
                $this->credentials['username'],
                $this->credentials['password']
            );

            $this->conn = new \PDO($conStr);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        } catch (\PDOException $e) {
            throw new Exception('Ошибка соединения с БД: ' . $e->getMessage());
        }

        if (empty($this->conn)) {
            throw new Exception('Нет соединения с БД');
        }
    }

    // Отключение от СУБД
    public function disconnect()
    {
        if (!empty($this->conn)) {
            $this->conn = null;
        }
    }

    public function logEnable($state = true)
    {
        $this->logEnabled = (bool)$state;
    }

    // Функция, экранирующая спецсимволы в строке запроса
    public static function escape_string($str)
    {
        $str = str_replace("''", "'", $str);

        return str_replace("'", "''", $str);
    }

    /*
     * Функция, экранирующая спецсимволы SQL в строке, и заключащая строку в кавычки
     * (включена для обратной совместимости; не рекомендуется к использованию)
     */
    public function quote_string($str)
    {
        return sprintf("'%s'", $this->escape_string($str));
    }

    // Функция, подготавливающая ассоциативный массив (кортежа) для использования в запросах
    public function prepare_array($rec): array
    {
        $rec_prep = [];
        foreach ($rec as $attr_name => $attr_value) {
            switch (gettype($attr_value)) {
                case 'NULL':
                    $rec_prep[$attr_name] = 'NULL';
                    break;
                case 'integer':
                case 'boolean':
                case 'double':
                    $rec_prep[$attr_name] = $attr_value;
                    break;
                case 'array':
                case 'object':
                    $rec_prep[$attr_name] = $this->quote_string(json_encode($attr_value));
                    break;
                default:
                    $rec_prep[$attr_name] = $this->quote_string($attr_value);
                    break;
            }
        }

        return $rec_prep;
    }

    public function str_replace_once($search, $replace, $text)
    {
        $pos = strpos($text, $search);

        return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }

    // Функция, собирающая из ассоциативного массива (кортежа) строку вида:
    // < атрибут1 = 'значение1', атрибут2 = 'значение2', ... >
    public function make_atoms_str($rec, $key_val_sep = ' = ', $list_sep = ', ')
    {
        $rec = $this->prepare_array($rec);
        $atoms = [];
        foreach ($rec as $attr_key => $attr_value) {
            $atoms[] = $attr_key . $key_val_sep . $attr_value;
        }

        return implode($list_sep, $atoms);
    }

    public function get_errors()
    {
        if (!empty($this->last_result)) {
            return null;
        }
        $errors = $this->last_result->errorInfo();
        if (empty($errors)) {
            return null;
        }
        if (!is_array($errors)) {
            return $errors;
        }
        $errors_str = [];
        if (count($errors) > 2) {
            $errors_str[] =
                sprintf(
                    '[%02d] SQLSTATE %d - CODE: %d - MESSAGE: %s',
                    $errors[0],
                    $errors[1],
                    $this->last_result->errorCode(),
                    $errors[2]
                );
        }

        return implode("<br/>\n", $errors_str);
    }

    // Исполнение запроса к БД без логирования (функция недоступна извне)
    protected function shadow_query($sql, $parameters = null)
    {
        if (empty($this->conn)) {
            return null;
        }

        if ($parameters) {
            foreach ($parameters as $par) {
                $sql = $this->str_replace_once('?', "'" . $par . "'", $sql);
            }
        }

        try {
            $result = $this->conn->query($sql);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $result = false;
        }

        if ($result === false || (!is_resource($result) && !$result)) {
            throw new Exception('Ошибка при выполнении SQL-запроса: ' . $sql . '<br />Сообщение об ошибке: ' . $message);
        }

        return $result;
    }

    // Исполнение запроса к БД без логирования (функция недоступна извне)
    public function exec($sql, $parameters = null)
    {
        if (empty($this->conn)) {
            return null;
        }

        if ($parameters) {
            foreach ($parameters as $par) {
                $sql = $this->str_replace_once('?', "'" . $par . "'", $sql);
            }
        }

        try {
            $this->conn->exec($sql);

        } catch (Exception $e) {
            throw new Exception('Ошибка при выполнении SQL-запроса: ' . $sql . '<br />Сообщение об ошибке: ' . $e->getMessage());
        }
    }

    // Исполнение запроса к БД с логированием
    public function query($sql, $parameters = null)
    {
        $ts_start = microtime(true);
        if ($parameters) {
            foreach ($parameters as $par) {
                $sql = $this->str_replace_once('?', "'" . $par . "'", $sql);
            }
        }
        $this->last_result = $this->shadow_query($sql, null);
        $ts_end = microtime(true);

        if ($this->logEnabled === true) {
            $this->shadow_query(
                sprintf( /** @lang */
                    "insert into log_db_query(user_id,query_sql,query_time,query_errors)values(0,'%s',%d,'%s')",
                    $this->quote_string($sql),
                    ($ts_end - $ts_start),
                    $this->get_errors()
                )
            );
        }

        return $this->last_result;
    }

    // Исполнение стандартного запроса на вставку кортежа в таблицу
    public function insert($table_name, $rec, $capture_inserted_id = null, $id_data_type = 'int')
    {
        $rec = $this->prepare_array($rec);
        if (empty($capture_inserted_id)) {
            $this->query(
                sprintf( /** @lang */
                    'INSERT INTO %s (%s) VALUES (%s)',
                    $table_name,
                    implode(', ', array_keys($rec)),
                    implode(', ', array_values($rec))
                )
            );
            $result = null;
            $this->last_result = null;
        } else {
            $result = $this->query_value(
                sprintf(/** @lang */
                    '
                  INSERT INTO %s (%s)
                  VALUES (%s)
                  RETURNING %s 
                  ',
                    $table_name,
                    implode(', ', array_keys($rec)),
                    implode(', ', array_values($rec)),
                    $capture_inserted_id
                )
            );
        }

        return $result;
    }

    public function insert_x_update($table_name, $rec, $keys)
    {
        $criteria = [];
        foreach ($keys as $key_name => $key_value) {
            $criteria[] = $key_name . ' = ?';
        }
        $criteria_str = sprintf('(%s)', implode(') AND (', $criteria));

        $update_fields = [];
        foreach ($rec as $field_name => $field_value) {
            $update_fields[] = $field_name . ' = ?';
        }
        $update_str = implode(', ', $update_fields);

        $entire_row = $keys + $rec;

        $query =
            sprintf(
                'IF EXISTS (SELECT * FROM %1$s WHERE %2$s)
                  UPDATE %1$s SET %3$s WHERE %2$s
               ELSE
                  INSERT INTO %1$s (%4$s) VALUES (%5$s)',
                $table_name,
                $criteria_str,
                $update_str,
                implode(', ', array_keys($entire_row)),
                implode(', ', array_fill(0, count($entire_row), '?'))
            );

        $this->query(
            $query,
            array_merge(
                array_values($keys),
                array_values($rec),
                array_values($keys),
                array_values($entire_row)
            )
        );
    }

    // Исполнение стандартного запроса на обновление кортежа в таблице
    // в наиболее общем случае, когда первичным ключем является составной атрибут
    // (ключевые атрибуты и их соответствующие значения должны быть перечислены в массиве $keys)
    public function update($table_name, $rec, $keys)
    {
        if (empty($keys)) {
            throw new Exception('Ошибка внутренней логики: недопустимый вызов dbxe::update().');
        }
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        $this->query(
            sprintf(
                'UPDATE %s SET %s WHERE %s',
                $table_name,
                $this->make_atoms_str($rec),
                $this->make_atoms_str($keys, ' = ', ' AND ')
            )
        );
        $this->last_result = null;
    }

    // Запрос, возвращающий результат как массив кортежей,
    // каждый из которых в свою очередь представлен массивом (по умолчанию - ассоциативным)
    public function query_array($sql, $parameters = null)
    {
        $this->query($sql, $parameters);
        if (empty($this->last_result) or !$this->skip_void_result_sets()) {
            $this->last_result = null;

            return null;
        }
        $result_set = [];
        while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
            $result_set[] = $row;
        }
        $this->last_result = null;

        return $result_set;
    }

    /*
     * Функция, специфичная для MS SQL Server, и возвращающая ВСЕ
     * массивы результатов для одного конкретного SQL-запроса.
     */
    public function mssql_xquery_array($sql, $result_set_modes = null, $monitor_errorlevel = null)
    {
        $this->query($sql);
        $result_sets = [];

        if (empty($this->last_result) or !$this->skip_void_result_sets()) {
            return null;
        }

        do {
            if ($this->last_result->columnCount() === 0) { // это не резалт-сет!
                continue;
            }

            $field_names = $this->get_field_meta('name');
            if (!empty($monitor_errorlevel)
                and count($field_names) === 1
                and reset($field_names) === $monitor_errorlevel) {
                $result_sets[] = array_shift($this->last_result->fetch(\PDO::FETCH_ASSOC));
                break;
            }
            $current_result_set = [];
            $current_result_set_mode = (!empty($result_set_modes) and is_array($result_set_modes)) ? array_shift($result_set_modes) : null;

            if (preg_match('#^deep-hash\((\d+)\)-(values|rows|value-groups|columns|row-groups|groups)$#', $current_result_set_mode, $matches)) {
                $depth = intval($matches[1]);
                $mode = $matches[2];
                $current_result_set = [];
                if ($depth < $this->last_result->columnCount()) {
                    while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
                        $pointer = &$current_result_set;
                        for ($i = 0; $i < $depth; $i++) {
                            $pointer = &$pointer[array_shift($row)];
                        }
                        if ($mode === 'values') {
                            $pointer = array_shift($row);
                        } elseif ($mode === 'rows') {
                            $pointer = $row;
                        } elseif ($mode === 'value-groups' || $mode === 'columns') {
                            $pointer[] = array_shift($row);
                        } elseif ($mode === 'row-groups' || $mode === 'groups') {
                            $pointer[] = $row;
                        }
                    }
                }
            } else {
                switch ($current_result_set_mode) {
                    case 'column':
                        while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
                            $current_result_set[] = array_shift($row);
                        }
                        break;
                    case 'hash':
                    case 'hash-values':
                        while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
                            $current_result_set[array_shift($row)] = array_shift($row);
                        }
                        break;
                    case 'hash-value-groups':
                        while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
                            $current_result_set[array_shift($row)][] = array_shift($row);
                        }
                        break;
                    case 'hash-rows':
                        while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
                            $current_result_set[array_shift($row)] = $row;
                        }
                        break;
                    case 'hash-rows-groups':
                        while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
                            $current_result_set[array_shift($row)][] = $row;
                        }
                        break;
                    case 'row':
                    case 'single-row':
                        $current_result_set = $this->last_result->fetch(\PDO::FETCH_ASSOC);
                        break;
                    case 'value':
                    case 'scalar-value':
                        $first_row = $this->last_result->fetch(\PDO::FETCH_ASSOC);
                        if (!empty($first_row) and is_array($first_row)) {
                            $current_result_set = array_shift($first_row);
                        }
                        break;
                    default:
                        while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
                            $current_result_set[] = $row;
                        }
                }
            }
            $result_sets[] = $current_result_set;
        } while ($this->last_result->nextRowset());
        $this->last_result = null;

        return $result_sets;
    }

    public function query_deep_hash($sql, $depth, $mode)
    {
        $this->query($sql);
        if (empty($this->last_result) or !$this->skip_void_result_sets()) {
            return null;
        }
        $result_set = [];
        if ($depth < $this->last_result->columnCount()) {
            while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
                $pointer = &$result_set;
                for ($i = 0; $i < $depth; $i++) {
                    $pointer = &$pointer[array_shift($row)];
                }
                if ($mode === 'values') {
                    $pointer = array_shift($row);
                } elseif ($mode === 'rows') {
                    $pointer = $row;
                } elseif ($mode === 'value-groups' || $mode === 'columns') {
                    $pointer[] = array_shift($row);
                } elseif ($mode === 'row-groups' || $mode === 'groups') {
                    $pointer[] = $row;
                }
            }
        }
        $this->last_result = null;

        return $result_set;
    }

    public function query_custom_hash($sql, $nesting, $mode)
    {
        $this->query($sql);
        if (empty($this->last_result) or !$this->skip_void_result_sets()) {
            return null;
        }
        if (!is_array($nesting) && $nesting != '') {
            $nesting = [$nesting];
        }
        $field_names = $this->get_field_meta('name');
        $nesting = array_intersect(array_unique($nesting), $field_names);
        $result_set = [];
        while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
            $pointer = &$result_set;
            foreach ($nesting as $nkey) {
                $pointer = &$pointer[$row[$nkey]];
                unset($row[$nkey]);
            }
            if ($mode === 'values') {
                $pointer = array_shift($row);
            } elseif ($mode === 'rows') {
                $pointer = $row;
            } elseif ($mode === 'value-groups' || $mode === 'columns') {
                $pointer[] = array_shift($row);
            } elseif ($mode === 'row-groups' || $mode === 'groups') {
                $pointer[] = $row;
            }
        }
        $this->last_result = null;

        return $result_set;
    }

    // Запрос, возвращающий единственное значение из первого полученного кортежа результата.
    // Следует использовать для получения ответов на запросы которые и должны вернуть единственный кортеж/значение
    public function query_value($sql, $parameters = null)
    {
        $this->query($sql, $parameters);
        if (empty($this->last_result) or !$this->skip_void_result_sets()) {
            $this->last_result = null;

            return null;
        }
        $row = $this->last_result->fetchColumn();
        if (empty($row)) {
            $this->last_result = null;

            return null;
        }
        $result_value = $row;
        $this->last_result = null;

        return $result_value;
    }

    // То же что предыдущий вызов, но возвращает весь первый ряд целиком
    public function query_row($sql, $parameters = null)
    {
        $this->query($sql, $parameters);
        if (empty($this->last_result) or !$this->skip_void_result_sets()) {
            return null;
        }
        $row = $this->last_result->fetch(\PDO::FETCH_ASSOC);
        $this->last_result = null;

        return $row;
    }

    // Возвращает отдельный столбец из результата как одномерный массив, извлекая первую ячейку из каждой строки
    public function query_column($sql, $parameters = null)
    {
        $this->query($sql, $parameters);
        if (empty($this->last_result) or !$this->skip_void_result_sets()) {
            return null;
        }
        $res_arr = [];
        while ($row = $this->last_result->fetch(\PDO::FETCH_NUM)) {
            $res_arr[] = reset($row);
        }
        $this->last_result = null;

        return $res_arr;
    }

    // Функция, возвращающая первые две колонки из результата запроса как хэщ-таблицу
    public function query_hash($sql, $parameters = null)
    {
        $this->query($sql, $parameters);
        if (empty($this->last_result) or !$this->skip_void_result_sets()) {
            return null;
        }
        $res_arr = [];
        while ($row = $this->last_result->fetch(\PDO::FETCH_NUM)) {
            $res_arr[reset($row)] = next($row);
        }
        $this->last_result = null;

        return $res_arr;
    }

    // Функция, возвращающая результат две или более колонки колонки из результата
    // запроса как двумерный ассоциативный массив; ключами первой размерности берутся
    // значения первой колонки результата, а значениями являются ассоциативные массивы
    // из имен => значений всех остальных атрибутов результата для каждого кортежа
    public function query_hash_array($sql, $parameters = null)
    {
        $this->query($sql, $parameters);
        if (empty($this->last_result) or !$this->skip_void_result_sets()) {
            return null;
        }
        $res_arr = [];
        while ($row = $this->last_result->fetch(\PDO::FETCH_ASSOC)) {
            $res_arr[array_shift($row)] = $row;
        }
        $this->last_result = null;

        return $res_arr;
    }

    public function setDateFormat($fmt = 'dmy')
    {
        if (preg_match('#^(mdy|dmy|ymd|ydm|myd|dym)$#', $fmt)) {
            $this->query('SET DATEFORMAT ' . $fmt);
        }
    }

    public static function DateFormat_ISO8601_msec($ts, $precision = 3)
    {
        return date(DATETIME_FMT_ISO8601, $ts) . strstr(round(fmod($ts, 1), $precision), '.');
    }

    protected function skip_void_result_sets()
    {
        do {
            if ($this->last_result->columnCount() === 0) { // это не резалт-сет!
                continue;
            } else {
                return 1;
            }
        } while ($this->last_result->nextRowset());

        return 0;
    }

    public function get_field_meta($property = 'name')
    {
        //return [];
        if (empty($this->last_result)) {
            return [];
        }
        $field_property = [];
        foreach (range(0, $this->last_result->columnCount() - 1) as $column_index) {
            $meta = $this->last_result->getColumnMeta($column_index);
            if ($meta && isset($meta[$property])) {
                $field_property[] = $meta[$property];
            }
        }

        return $field_property;
    }

}
