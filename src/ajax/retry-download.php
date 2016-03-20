<?php
$id = \H::i("downloadID", "", "POST");

if($id != "" && $this->downloadExists($id)){
  $dInfo = getJSONData($id);
  $savePath = $dInfo['downloadDir'] . DIRECTORY_SEPARATOR . $dInfo['fileName'];
  
  /**
   * Remove downloaded file
   */
  unlink($savePath);
  
  /**
   * Make percentage to 0 to indicate download not completed
   */
  saveJSONData($id, array(
    "percentage" => "0",
    "error" => "0"
  ));
}
