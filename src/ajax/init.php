<?php
/**
 * Start Downloads if not started
 */
if(!$this->isDownloadRunning()){
  /**
   * The downloads to do
   */
  $doDs = array();
  
  /**
   * The existing downloads
   */
  $ds = array_keys(getJSONData("downloads"));
  foreach($ds as $dName){
    $dInfo = getJSONData($dName);
    if($dInfo['paused'] === "0" && $dInfo['percentage'] != "100"){
      $doDs[$dName] = $dInfo;
    }
  }
  
  if(count($doDs) !== 0){
    /**
     * We're escaping double quotes(")
     */
    $doDsJSON = str_replace('"', '\\"', json_encode($doDs));
    $command = '"' . $this->getPHPExecutable() .'" "'. __DIR__ .'/background-download.php" "'. APP_URL .'/receive-status" "'. $doDsJSON .'" > "' . __DIR__ . '/shell-out.txt" &';
    
    if(\Lobby::$sysInfo['os'] === "windows"){
      $WshShell = new COM("WScript.Shell");
      $oExec = $WshShell->Run($command, 0, false);
    }else{
      exec($command);
    }
    $this->log("Background download executed command : $command");
    echo json_encode(array(
      "status" => "started",
      "active" => array_keys($doDs)
    ));
  }
}else{
  echo json_encode(array(
    "status" => "running"
  ));
}
