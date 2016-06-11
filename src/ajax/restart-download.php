<?php
$id = \Request::postParam("downloadID", "");

if($id != "" && $this->downloadExists($id)){
  $dInfo = $this->getJSONData($id);
  $savePath = $dInfo['downloadDir'] . DIRECTORY_SEPARATOR . $dInfo['fileName'];
  
  if($dInfo["percentage"] == "100"){
    /**
     * File was downloaded completely, so
     * Remove downloaded file and start again
     */
    unlink($savePath);
    
    /**
     * Make percentage to 0 to indicate download not completed
     */
    saveJSONData($id, array(
      "percentage" => "0",
      "error" => "0"
    ));
  }else{
    /**
     * Remove error and re start at the current state
     */
    saveJSONData($id, array(
      "error" => "0"
    ));
  }
  
  $this->refreshDownloads();
}
