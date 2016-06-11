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
        $this->url . '/receive-status',
        $doDsJSON
      )
    ));
    
    $moduleInit = isset($moduleInit);
    
    $command = $Process->start(function() use ($doDs, $moduleInit){
      /**
       * If init.php is included by Module, then no need of output
       */
      if(!$moduleInit){
        echo json_encode(array(
          "status" => "started",
          "active" => array_keys($doDs)
        ));
      }
    });
    $this->saveData("lastDownloadStatusCheck", time() + 10);

    $this->log("Background download executed command : $command");
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
