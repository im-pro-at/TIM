<?php
/*
  SQL.php
  
  Autor: im-pro
*/

class SQL_RESULT {
  var $mysqli;
  var $result;
  function __construct($mysqli,$result) 
  { 
    $this->mysqli=$mysqli;
    $this->result=$result;
  }

  //Returns the number of rows affected by INSERT, UPDATE, or DELETE query.
  function affected_rows()
  {
    return $this->mysqli->affected_rows;
  }
  //Returns the ID of an INSERT query
  function insert_id()
  {
    return $this->mysqli->insert_id;
  }
  //Anzahl der ergebnisse
  function num_rows()
  {
    return $this->result->num_rows;    
  }
  //gibt alles zurück in einem Array mit ergebnis arrays
  function fetch_all()
  {
    return $this->result->fetch_all(MYSQLI_ASSOC);    
  }
  //returns one row or NULL if empty
  function fetch()
  {
    return $this->result->fetch_array(MYSQLI_ASSOC);    
  }
  //frees memory
  function free()
  {
    return $this->result->free();    
  }
}
class SQL {

  var $mysqli;
  var $errorcallback;
  
  function __construct($server_sql, $benutzer_sql, $passwort_sql, $dbname_sql, $errorcallback) 
  { 
    $this->errorcallback=$errorcallback;
    $this->mysqli=new mysqli(server_sql, benutzer_sql, passwort_sql, dbname_sql);
    if ($this->mysqli->connect_errno) 
    {
      call_user_func_array ( $this->errorcallback , array("MySQL",'Connect Error ('. $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error . "\n Check your settings in 'Settings.php'!"));
    }
    if (!$this->mysqli->set_charset("utf8mb4")) {
      call_user_func_array ( $this->errorcallback , array("MySQL","Error loading character set utf8mb4: ". $this->mysqli->error));
    }    
  } 
  function escape($var){
    return $this->mysqli->real_escape_string($var);
  }
  function query($sql,$reporterror=1)
  {
    print_debug("sqlquery",$sql);
    $result=$this->mysqli->query($sql);
    if(!$result)
    {
      if($reporterror)
        call_user_func_array ( $this->errorcallback , array("MySQL","Error query sql statement(".$sql."): Error Code (". $this->mysqli->errno . ")". $this->mysqli->error));
      else 
        return null;
    }
    return new SQL_RESULT($this->mysqli,$result);
  }
  function close()
  {
    if(!$this->mysqli->close())
    {
      call_user_func_array ( $this->errorcallback , array("MySQL","Error closing sql connection: ". $this->mysqli->error));
    }
  }
}
?>