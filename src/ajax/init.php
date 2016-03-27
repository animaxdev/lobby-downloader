<?php
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
  foreach($this->ds as $dName){
    $dInfo = \H::getJSONData($dName);
    if($dInfo['paused'] == "0" && $dInfo['percentage'] != "100"){
      $doDs[$dName] = $dInfo;
    }
  }
  
  if(count($doDs) !== 0){
    /**
     * We're escaping double quotes(")
     */
    $doDsJSON = str_replace('"', '\\"', json_encode($doDs));
    $command = '"' . $this->getPHPExecutable() .'" "'. __DIR__ .'/background-download.php" "'. APP_URL .'/receive-status" "'. $doDsJSON .'" > "' . __DIR__ . '/shell-out.txt" &';

    $process = new Process($command);
    $process->start();

    $this->log("Background download executed command : $command");
    echo json_encode(array(
      "status" => "started",
      "active" => array_keys($doDs)
    ));
  }else{
    echo json_encode(array(
      "status" => "world-is-great"
    ));
  }
}else{
  echo json_encode(array(
    "status" => "running"
  ));
}
