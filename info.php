<?php 
$DB_Host = "localhost";
$DB_Name="igendemo_c2c";//
$DB_Username="igendemo_c2c";
$DB_Password="Root12345#";
error_reporting(E_ALL);
$display_record_per_page= 10;
$connection_1 = mysqli_connect($DB_Host,$DB_Username,$DB_Password,$DB_Name);
if(mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}else{
    echo 'ddddddddddddddddddd';
}

//echo phpinfo();



?>