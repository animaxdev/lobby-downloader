<?php
$url = \H::i("downloadURL", "", "POST");
$dDir = \H::i("downloadPath", "", "POST");

if($url != "" && $dDir != ""){
  $file_headers = @get_headers($url);
  if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
    echo "urlNotFound";
  }else{
    $p = parse_url($url);
    $fileName = basename($p['path']);
    $this->addDownload($url, $fileName, $dDir);
  }
}
