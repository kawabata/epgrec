
<?php
include_once('config.php');
include_once(INSTALL_PATH."/DBRecord.class.php");
include_once(INSTALL_PATH."/reclib.php");
include_once(INSTALL_PATH."/Settings.class.php");

$settings = Settings::factory();

if( !isset( $_POST['reserve_id'] ) ) {
	exit("Error: IDが指定されていません" );
}
$reserve_id = $_POST['reserve_id'];

$dbh = false;
if( $settings->mediatomb_update == 1 ) {
	$dbh = @($GLOBALS["___mysqli_ston"] = mysqli_connect( $settings->db_host,  $settings->db_user,  $settings->db_pass ));
	if( $dbh !== false ) {
		$sqlstr = "use ".$settings->db_name;
		@mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
		$sqlstr = "set NAME utf8";
		@mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
	}
}

try {
	$rec = new DBRecord(RESERVE_TBL, "id", $reserve_id );
	
	if( isset( $_POST['title'] ) ) {
		$rec->title = trim( $_POST['title'] );
		$rec->dirty = 1;
		if( ($dbh !== false) && ($rec->complete == 1) ) {
			$title = trim( ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['title']) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")));
			$title .= "(".date("Y/m/d", toTimestamp($rec->starttime)).")";
			$sqlstr = "update mt_cds_object set dc_title='".$title."' where metadata regexp 'epgrec:id=".$reserve_id."$'";
			@mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
		}
	}
	
	if( isset( $_POST['description'] ) ) {
		$rec->description = trim( $_POST['description'] );
		$rec->dirty = 1;
		if( ($dbh !== false) && ($rec->complete == 1) ) {
			$desc = "dc:description=".trim( ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['description']) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")));
			$desc .= "&epgrec:id=".$reserve_id;
			$sqlstr = "update mt_cds_object set metadata='".$desc."' where metadata regexp 'epgrec:id=".$reserve_id."$'";
			@mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
		}
	}
}
catch( Exception $e ) {
	exit("Error: ". $e->getMessage());
}

exit("complete");

?>