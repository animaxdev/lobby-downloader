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
    $doDsJSON = json_encode($doDs);
    $command = $this->getPHPExecutable() ." '". __DIR__ ."/background-download.php' '". APP_URL ."/receive-status' '$doDsJSON' > " . __DIR__ . "/shell-out.txt &";
    exec($command);
    $this->log("BG download executed command : $command");
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
