<?php
  /*$json_str = file_get_contents('php://input');
  $json = json_decode($json_str);
  var_dump($json->EventDateTime->date);*/
  session_start();
  echo $_SESSION["user"];
?>