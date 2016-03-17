<?php
$dName = \H::i("downloadName", "", "POST");

if($dName != ""){
  removeData($dName);
  saveJSONData("downloads", array(
    $dName => false
  ));
}
