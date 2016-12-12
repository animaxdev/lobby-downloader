<?php
$id = \Request::postParam("downloadID", "");

if($id != "" && $this->downloadExists($id)){
  $dInfo = $this->data->getArray($id);
  $savePath = $dInfo['downloadDir'] . DIRECTORY_SEPARATOR . $dInfo['fileName'];

  if(file_exists($savePath)){
    /**
     * File was downloaded completely, so
     * Remove downloaded file and start again
     */
    unlink($savePath);
  }

  /**
   * Remove error and re start at the current state
   * Make percentage to 0 to indicate download not completed
   */
  $this->data->saveArray($id, array(
    "error" => "0",
    "paused" => "0",
    "percentage" => "0",
  ));

  $this->refreshDownloads();
}
