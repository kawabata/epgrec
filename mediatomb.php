
#!/usr/bin/php
<?php
$script_path = dirname( __FILE__ );
chdir( $script_path );
include_once( $script_path . '/config.php');
include_once(INSTALL_PATH.'/DBRecord.class.php');
include_once(INSTALL_PATH.'/reclib.php');
include_once(INSTALL_PATH.'/Settings.class.php');

$settings = Settings::factory();

try {

  $recs = DBRecord::createRecords(RESERVE_TBL );

// DB接続
  $dbh = ($GLOBALS["___mysqli_ston"] = mysqli_connect( $settings->db_host,  $settings->db_user,  $settings->db_pass ));
  if( $dbh === false ) exit( "mysql connection fail" );
  $sqlstr = "use ".$settings->db_name;
  mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
  $sqlstr = "set NAME utf8";
  mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );

  foreach( $recs as $rec ) {
	  $title = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $rec->title) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""))."(".date("Y/m/d", toTimestamp($rec->starttime)).")";
      $sqlstr = "update mt_cds_object set metadata='dc:description=".((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $rec->description) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""))."&epgrec:id=".$rec->id."' where dc_title='".$rec->path."'";
      mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
      $sqlstr = "update mt_cds_object set dc_title='".$title."' where dc_title='".$rec->path."'";
      mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
  }
}
catch( Exception $e ) {
    exit( $e->getMessage() );
}
?>
