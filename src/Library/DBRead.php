<?php

namespace App\Library;

use Exception;

final class DBRead
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
        if ($parameters) {
            foreach ($parameters as $par) {
                $sql = $this->str_replace_once('?', "'" . $par . "'", $sql);
            }
        }
        $this->last_result = $this->shadow_query($sql, null);

        return $this->last_result;
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
