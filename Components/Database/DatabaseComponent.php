<?php
/**
 * Created by PhpStorm.
 * User: ÐñÑô
 * Date: 2016/2/6
 * Time: 13:25
 */

namespace Database;


class DatabaseComponent extends \Block
{
    public $database = "";
    public $host = "";
    public $username = "";
    public $password = "";
    public $connent = "";
    public $table = "";
    public $result = "";
    public $column = "";
    public $field = "*";
    public $condition = array(0=>"",1=>array());

    public function Start()
    {
        $this->connent = new \PDO("mysql:host={$this->host};dbname={$this->database}", $this->username, $this->password);
    }

    public function view($sql)
    {
        if (preg_match("/select[\s\S]*?from/i", $sql)) {
            $this->result = $this->connent->query($sql)->fetch();
            return $this->result;
        } else {
            $this->table = $sql;
            $result = $this->connent->query("desc `$sql`");
            if($result)
            {
                foreach ($result as $value) {
                    $this->column = $value;
                }
            }
            return $this;
        }
    }

    public function field()
    {
        if (func_num_args()) {
            $this->field = func_get_args()[0];
        } else {
            $this->field = "*";
        }
        return $this;
    }

    private function clear()
    {
        $this->field = "*";
        $this->condition = array(0=>"",1=>array());
    }

    public function select()
    {
        $sql = "select $this->field from $this->table " . (($this->condition[0]) ? "where {$this->condition[0]}" : "");
        $result = $this->connent->prepare($sql);
        $result->execute($this->condition[1]);
        if(!$result)
        {
            \Errors::Exception($this->result->errorInfo()[2]);
        }
        $this->result = new \stdClass();
        $this->result->num = $result->rowCount();
        $this->result->rows[] = $this->result->row = $result->fetch();
        while ($this->result->rows[] = $result->fetch()) ;
        $this->clear();
        return $this->result;
    }

    public function where()
    {
        $condition = func_get_args();

        if ($this->condition[0] == "") {
            $this->condition[0] = $condition[0];
        } else {
            if ($this->condition[0]) {
                $this->condition[0] = "{$this->condition[0]} and $condition[0]";
            }
        }

        if(isset($condition[1]))
        {
            $this->condition[1]=array_merge($this->condition[1],$condition[1]);
        }
        return $this;
    }

    public function insert($array)
    {

        foreach ($array as $key => $value) {
            if (!isset($field)) {
                $field = "`$key`";
                $column = "'$value'";
            } else {
                $field .= ",`$key`";
                $column .= ",'$value'";
            }
        }
        $sql = "insert into `$this->table` ($field)values($column)";
        $this->result = $this->connent->prepare($sql);
        $this->result->execute($this->condition[1]);
        if (!$this->result) {
            return $this->result;
        } else {
            \Errors::Exception($this->result->errorInfo()[2]);
            return $this->connent->lastInsertId();
        }

    }

    public function delete()
    {
        $sql = "delete from $this->table where {$this->condition[0]}";
        $this->result = $this->connent->prepare($sql);
        $this->result->execute($this->condition[1]);
        if(!$this->result)
        {
            \Errors::Exception($this->result->errorInfo()[2]);
        }
        $this->clear();
    }

    public function update($array)
    {
        foreach ($array as $key => $value) {
            if (!isset($set) && !isset($column)) {
                $set = "`$key`='$value'";
            } else {
                $set .= ",`$key`='$value'";
            }
            $sql = "update `$this->table` set $set where $this->condition[0]";
            $this->result = $this->connent->prepare($sql);
            $this->result->execute($this->condition[1]);
            $this->clear();
            if ($this->result) {
                return true;
            } else {
                \Errors::Exception($this->result->errorInfo()[2]);
                return false;
            }
        }

    }
}