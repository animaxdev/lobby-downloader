<?php
$dName = \Request::postParam("downloadName", "");

if($dName != ""){
  $this->refreshDownloads();
  $this->data->remove($dName);
  $this->data->saveArray("downloads", array(
    $dName => false
  ));
}
