<?php
if(!function_exists("getData")){
  require_once __DIR__ . "/../../../../../load.php";
  
}
/**
 * Start Downloads if not started
 */
if(getData("download_active") === null){
  set_time_limit(0);
  
  $doDs = array();
  $ds = array_keys(getJSONData("downloads"));
  foreach($ds as $dName){
    $dInfo = getJSONData($dName);
    if($dInfo['paused'] == "0"){
      $doDs[$dName] = $dInfo;
    }
  }
  $this->download($doDs);
  system(PHP_BINARY . " ".__FILE__);
}
