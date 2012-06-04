<?php
 
FUNCTION send_file($name) {
  OB_END_CLEAN();
  $path = $name; //"../temp/".$name;
  IF (!IS_FILE($path) or CONNECTION_STATUS()!=0) RETURN(FALSE);
  HEADER("Cache-Control: no-store, no-cache, must-revalidate");
  HEADER("Cache-Control: post-check=0, pre-check=0", FALSE);
  HEADER("Pragma: no-cache");
  HEADER("Expires: ".GMDATE("D, d M Y H:i:s", MKTIME(DATE("H")+2, DATE("i"), DATE("s"), DATE("m"), DATE("d"), DATE("Y")))." GMT");
  HEADER("Last-Modified: ".GMDATE("D, d M Y H:i:s")." GMT");
  HEADER("Content-Type: application/octet-stream");
  HEADER("Content-Length: ".(string)(FILESIZE($path)));
  HEADER("Content-Disposition: inline; filename=$name");
  HEADER("Content-Transfer-Encoding: binary\n");
  IF ($file = FOPEN($path, 'rb')) {
   WHILE(!FEOF($file) and (CONNECTION_STATUS()==0)) {
     PRINT(FREAD($file, 1024*8));
     FLUSH();
   }
   FCLOSE($file);
  }
  RETURN((CONNECTION_STATUS()==0) and !CONNECTION_ABORTED());
}
 
 
IF (!send_file($_GET['file'])) {
DIE ("file transfer failed");
 
// either the file transfer was incomplete
// or the file was not found
 
} ELSE {

// the download was a success
// log, or do whatever else
}
 
?> 