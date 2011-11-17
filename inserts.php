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
    private $field = "";
    private $type = "";
    private $mode = "";

    public function __construct($path, $table, $field, $type, $mode, $path_sql) {
        $this->path = $path;
        $this->table = $table;
        $this->field = $this->quote($field);
        $this->type = $type;
        $this->mode = $mode;
	$this->path_sql = $path_sql;
    }

    private function db() {

        $connection = @mysql_connect("host", "username", "password");

        if (!$connection) { echo("connection not available");exit;}

        if (!mysql_select_db("database")) {echo("no database selected");exit;}
        
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
            
            $sql = "SELECT " . implode($this->field, ",") . " FROM " . $this->table . "
                        WHERE 1";
            $result = mysql_query($sql);
            if (!$result) {
                echo("sql cannot be executed");exit;
            }

            while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
                $data_n = $this->string_type($row, $this->type);
                array_push($template, implode($data_n, ","));
            }
        }
        return "LOCK TABLES `" . $this->table . "` WRITE;\nINSERT INTO `" . $this->table . "` (" . implode($this->field, ',') . ") VALUES\n"
                . implode($this->wrap($template), ",") . ";\nUNLOCK TABLES;";
    }

    public function write_file(){
	$fp = fopen($this->path_sql."dump.sql", 'w+');
	fwrite($fp, $this->init());
	fclose($fp);
    }

}

//csv path
$path = "C:\\data.csv";

//sql ouput file path
$path_sql = "C:\\Users\\Username\\Desktop\\";
//table
$table = "user";
//field
$field = array('signin_id', 'name', 'email');
//field type
$type = array('n', 's', 's');
//csv/db
$mode = "db";

$a = new inserts($path, $table, $field, $type, $mode, $path_sql);
$a->write_file();
?>