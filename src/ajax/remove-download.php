<?php
$dName = \H::i("downloadName", "", "POST");

if($dName != ""){
  $this->refreshDownloads();
  removeData($dName);
  saveJSONData("downloads", array(
    $dName => false
  ));
}
