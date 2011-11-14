<?php

/**
  * Enter description here ...
    * @author tsuyu
      *
        */
	  
	  class inserts {

	      private $path = "";
	          private $table = "";
		      private $field = "";
		          private $type = "";
			      private $mode = "";

			          public function __construct($path, $table, $field, $type, $mode) {
				          $this->path = $path;
					          $this->table = $table;
						          $this->field = $this->quote($field);
							          $this->type = $type;
								          $this->mode = $mode;
									      }

									          private function db() {

										          $connection = @mysql_connect("localhost", "root", "123456");

											          if (!$connection) { echo("connection not available");exit;}

												          if (!mysql_select_db("tsuyu")) {echo("no database selected");exit;}
													          
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
																																																																										          return "INSERT INTO `" . $this->table . "` (" . implode($this->field, ',') . ") VALUES\n"
																																																																											                  . implode($this->wrap($template), ",") . ";";
																																																																													      }

																																																																													      }

																																																																													      //csv path
																																																																													      $path = "C:\\Users\\tsuyu_7\\Desktop\\backup_pknp\\data.csv";
																																																																													      //table
																																																																													      $table = "user";
																																																																													      //field
																																																																													      $field = array('signin_id', 'name', 'email');
																																																																													      //field type
																																																																													      $type = array('n', 's', 's');
																																																																													      //csv/db
																																																																													      $mode = "db";

																																																																													      $a = new inserts($path, $table, $field, $type, $mode);
																																																																													      echo $a->init();
																																																																													      ?>
