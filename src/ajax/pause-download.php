<?php
$id = \H::i("downloadID", "", "POST");

if($id != ""){
  /**
   * Make percentage to 0 to indicate download not completed
   */
  saveJSONData($id, array(
    "paused" => isset($_POST['resume']) ? "0" : "1"
  ));
}
