<?php
use Fr\Process;

/**
 * Start Downloads if not started
 */
if(!$this->isDownloadRunning()){
  /**
   * The downloads to do
   */
  $doDs = $this->getActiveDownloads();
  
  if(count($doDs) !== 0){
    /**
     * We're escaping double quotes(")
     */
    $doDsJSON = json_encode($doDs);
    
    $Process = new Process($this->getPHPExecutable(), array(
      "arguments" => array(
        __DIR__ . '/background-download.php',
        $this->URL . '/receive-status',
        $doDsJSON
      )
    ));
    $command = $Process->start();
    $this->saveData("lastDownloadStatusCheck", time() + 10);

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
