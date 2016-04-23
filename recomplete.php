
#!/usr/bin/php
<?php
$script_path = dirname( __FILE__ );
chdir( $script_path );
include_once( $script_path . '/config.php');
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/Settings.class.php" );
include_once( INSTALL_PATH . "/recLog.inc.php" );

$settings = Settings::factory();

$reserve_id = $argv[1];

try{
	$rrec = new DBRecord( RESERVE_TBL, "id" , $reserve_id );
	$rrec->complete = '1';
	
	if( file_exists( INSTALL_PATH .$settings->spool . "/". $rrec->path ) ) {
		// 予約完了
		reclog( "recomplete:: 予約ID". $rrec->id .":".$rrec->type.$rrec->channel.$rrec->title."の録画が完了" );
		
		if( $settings->mediatomb_update == 1 ) {
			// ちょっと待った方が確実っぽい
			@exec("sync");
			sleep(15);
			$dbh = ($GLOBALS["___mysqli_ston"] = mysqli_connect( $settings->db_host,  $settings->db_user,  $settings->db_pass ));
			if( $dbh !== false ) {
				$sqlstr = "use ".$settings->db_name;
				@mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
				// 別にやらなくてもいいが
				$sqlstr = "set NAME utf8";
				@mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
				$sqlstr = "update mt_cds_object set metadata='dc:description=".((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $rrec->description) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""))."&epgrec:id=".$reserve_id."' where dc_title='".$rrec->path."'";
				@mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
				$sqlstr = "update mt_cds_object set dc_title='".((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $rrec->title) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""))."(".date("Y/m/d").")' where dc_title='".$rrec->path."'";
				@mysqli_query($GLOBALS["___mysqli_ston"],  $sqlstr );
			}
		}
	}
	else {
		// 予約失敗
		reclog( "recomplete:: 予約ID". $rrec->id .":".$rrec->type.$rrec->channel.$rrec->title."の録画に失敗した模様", EPGREC_ERROR );
		$rrec->delete();
	}
}
catch( exception $e ) {
	reclog( "recomplete:: 予約テーブルのアクセスに失敗した模様", EPGREC_ERROR );
	reclog( "recomplete:: ".$e->getMessage()."" , EPGREC_ERROR );
	exit( $e->getMessage() );
}

?>
