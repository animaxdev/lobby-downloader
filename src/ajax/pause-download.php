<?php
$id = \Request::postParam("downloadID", "");

if($id != ""){
  $this->refreshDownloads();
  
  /**
   * Make percentage to 0 to indicate download not completed
   */
  $this->saveJSONData($id, array(
    /**
     * Reverse, because we call refreshDownloads()
     */
    "paused" => isset($_POST['resume']) ? "0" : "1"
  ));
}
