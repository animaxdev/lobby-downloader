<?php
/**
 * Start Downloads if not started
 */
if(getData("download_active") !== null){
  $ds = getJSONData("downloads");
  foreach($ds as $dName){
    $this->download($dName);
  }
}
