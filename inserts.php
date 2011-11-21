<?php

/*
  This file is part of inserts.

  inserts is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  inserts is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with inserts.  If not, see <http://www.gnu.org/licenses/>.

  tsuyu
  inserts.php
  main class

 */

class inserts {

    private $path = "";
    private $path_sql = "";
    private $table = "";
    private $join_table = "";
    private $field = "";
    private $type = "";
    private $mode = "";
    private $criteria = "";
    private $divide = "";

    public function __construct($path, $table, $join_table, $field, $type, $mode, $path_sql, $criteria, $divide) {
        $this->path = $path;
        $this->table = $table;
        $this->join_table = $join_table;
        $this->field = $this->quote($field);
        $this->type = $type;
        $this->mode = $mode;
        $this->path_sql = $path_sql;
        $this->criteria = $criteria;
        $this->divide = $divide;
    }

    private function db() {

        $connection = @mysql_connect("host", "username", "password");

        if (!$connection) {
            echo("connection not available");
            exit;
        }

        if (!mysql_select_db("database")) {
            echo("no database selected");
            exit;
        }

        return $connection;
    }

    public function quote($field) {
        foreach ($field as $key => $value) {
            $field[$key] = "`" . $field[$key] . "`";
        }
        return $field;
    }

    public function string_type($data, $type) {
        foreach ($type as $key => $value) {
            if ($value == 's') {
                $data[$key] = "'" . $data[$key] . "'";
            }
        }
        return $data;
    }

    public function wrap($template) {
        foreach ($template as $key => $value) {
            $template[$key] = "(" . $template[$key] . ")";
        }
        return $template;
    }

    public function init() {

        $template = array();

        if ($this->mode == 'csv' && !empty($this->path)) {
            if (($handle = fopen($this->path, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $data_n = $this->string_type($data, $this->type);
                    array_push($template, implode($data_n, ","));
                }
            }
        } else if ($this->mode == 'db') {

            $this->db();

            $sql = "SELECT " . implode($this->field, ",") . " FROM " . $this->table . "";
            if (!empty($this->join_table)) {
                $sql .= $this->join_table;
            }
            $sql .= " WHERE 1 ";
            if (!empty($this->criteria)) {
                $sql .= $this->criteria;
            }
            $result = mysql_query($sql);

            if (!$result) {
                echo("sql cannot be executed");
                exit;
            }
            $i = 1;
            while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
                $data_n = $this->string_type($row, $this->type);
                $template[$i] = implode($data_n, ",");
                $i++;
            }
        } else {
            echo "please specify the mode";
            exit;
        }

        if (intval($this->divide)) {

            $range = range($this->divide, mysql_num_rows($result), $this->divide);

            foreach ($template as $key => $value) {
                foreach ($range as $key2 => $value2) {
                    if ($key > $range[$key2 - 1] && $key <= $value2) {
                        $e[$value2][] = $value;
                    } else if ($key > end($range)) {
                        $e[end($range) + 1][0] = $value;
                    }
                }
            }

            $r = '';
            foreach ($e as $key => $value) {
                $r .= "LOCK TABLES `" . $this->table . "` WRITE;\nINSERT INTO `" . $this->table . "` (" . implode($this->field, ',') . ") VALUES\n"
                        . implode($this->wrap($value), ",") . ";\nUNLOCK TABLES; \n";
            }
            return $r;
        } else if ($this->divide == 'd') {
            return "LOCK TABLES `" . $this->table . "` WRITE;\nINSERT INTO `" . $this->table . "` (" . implode($this->field, ',') . ") VALUES\n"
                    . implode($this->wrap($template), ",") . ";\nUNLOCK TABLES; \n";
        }
    }

    public function write_file($output) {
        if ($output == 'dump') {
            $fp = fopen($this->path_sql . "dump.sql", 'w+');
            fwrite($fp, $this->init());
            fclose($fp);
        } else if ($output == 'echo') {
            echo $this->init();
        } else {
            echo "please specify the output";
        }
    }

}

/* csv path */
$path = "C:\\data.csv";

/* sql ouput file path */
$path_sql = "C:\\Users\\Username\\Desktop\\";

/* table */
$table = "user";

/* join table if exist */
$join_table = '';

/* list of field */
$field = array('signin_id', 'name', 'email');

/* desc : general field type
 * option type:-
 * 	a) s - string
 * b) n - numeric
 */
$type = array('n', 's', 's');

/* desc : specify datasource mode
 * option mode:-
 * a) csv - comma seperated value file
 * b) db - mysql datasource
 */
$mode = "db";

/* desc : specify the output
 * option output:-
 * a) dump
 * b) echo
 */
$output = "echo";

/* if exist..
 * example :- "AND `signin_id` = 1"
 */
$criteria = "";

/* specify the devide of sql statement
 * d - default
 * 1...
 */
$divide = 'd';


$a = new inserts($path, $table, $join_table, $field, $type, $mode, $path_sql, $criteria, $divide);
$a->write_file($output);

?>