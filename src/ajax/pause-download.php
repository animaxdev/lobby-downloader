<?php
$id = \H::i("downloadID", "", "POST");

if($id != ""){
  $this->refreshDownloads();
  
  /**
   * Make percentage to 0 to indicate download not completed
   */
  \H::saveJSONData($id, array(
    /**
     * Reverse, because we call refreshDownloads()
     */
    "paused" => isset($_POST['resume']) ? "0" : "1"
  ));
}
