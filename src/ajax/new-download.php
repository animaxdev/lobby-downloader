<?php
$url = \H::i("downloadURL", "", "POST");

if($url != ""){
  $file_headers = @get_headers($url);
  var_dump($file_headers);
  if($file_headers[0] != 'HTTP/1.1 404 Not Found') {
    $this->addDownload($url);
  }
  
}
