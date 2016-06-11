<?php
$url = \Request::postParam("downloadURL", "");
$dDir = \Request::postParam("downloadPath", "");

if($url != "" && $dDir != ""){
  $file_headers = @get_headers($url, 1);
  if($file_headers == false || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
    echo "urlNotFound";
  }else{
    $p = parse_url($url);
    $fileName = basename($p['path']);
    $this->addDownload($url, $fileName, $dDir, isset($file_headers['Content-Length']));
    $this->refreshDownloads();
  }
}
