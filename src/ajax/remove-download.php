<?php
$dName = \Request::postParam("downloadName", "");

if($dName != ""){
  $this->refreshDownloads();
  $this->removeData($dName);
  saveJSONData("downloads", array(
    $dName => false
  ));
}
