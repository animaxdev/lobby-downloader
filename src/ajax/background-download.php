<?php
set_time_limit(0);
if(isset($argv[1])){
  $ds = json_decode($argv[2], true);
  $statusURL = $argv[1];
  
  function saveData($newDs){
    global $ds, $statusURL;
    $newDs = array_replace_recursive($ds, $newDs);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $statusURL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "status=" . json_encode($newDs));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $server_output = curl_exec($ch);
    if($server_output == "cancelled" || $server_output == "paused"){
      $keys = array_keys($newDs);
      $dName = $keys[0];
      $GLOBALS["curlCancel$dName"] = 1;
    }
    curl_close ($ch);
  }
  
  require_once __DIR__ . "/../lib/vendor/autoload.php";
  
  $mrHandler = new \MultiRequest\Handler();
  $mrHandler->setConnectionsLimit(1000);
  
  $GLOBALS['startTime'] = $GLOBALS['prevTime'] = microtime(true);
  $GLOBALS['prevSize'] = $GLOBALS['currentSpeed'] = $GLOBALS['timeRemaining'] = $GLOBALS['alreadyDownloaded'] = 0;
  
  $curlResult = array();
  
  foreach($ds as $dName => $dInfo) {
    $url = $dInfo['url'];
    $savePath = $dInfo['downloadDir'] . DIRECTORY_SEPARATOR . $dInfo['fileName'];
    
    $savePathFP = fopen($savePath, "a+");

    $curlOptions = array(
      CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
      CURLOPT_NOPROGRESS => false,
      CURLOPT_FOLLOWLOCATION => 1,
      CURLOPT_BINARYTRANSFER => true,
      CURLOPT_PROGRESSFUNCTION => function($resource, $downloadSize, $downloaded, $upload_size, $uploaded = "") use($dName) {
        /**
         * On new versions of cURL, $resource parameter is not passed
         * So, swap vars if it doesn't exist
         */
        if(!is_resource($resource)){
          $uploaded = $upload_size;
          $upload_size = $downloaded;
          $downloaded = $downloadSize;
          $downloadSize = $resource;
        }
        
        $dInfo = array(
          "error" => "0"
        );
        
        if($downloaded > 0 && $downloadSize > 0){
          /**
           * If the file is resumed for download, then the
           * $downloaded and $downloadSize will be the difference
           * of the actual file size - partially downloaded file size
           * So, to get the actual file size, we must add the
           * partial file size to it.
           * $GLOBALS['alreadyDownloaded'] = partial file size
           */
          $downloaded = $GLOBALS['alreadyDownloaded'] + $downloaded;
          $downloadSize = $GLOBALS['alreadyDownloaded'] + $downloadSize;
          
          $dInfo["downloaded"] = $downloaded;
          $dInfo["size"] = $downloadSize;
          $dInfo['percentage'] = ($downloaded / $downloadSize) * 100;
        }
        
        if($GLOBALS['prevTime'] < strtotime("-1 second")){
          /**
           * Calculate Speed
           */
          $GLOBALS['averageSpeed'] = $downloaded / (microtime(true) - $GLOBALS['startTime']);
          
          $GLOBALS['currentSpeed'] = round(($downloaded - $GLOBALS['prevSize']) / (microtime(true) - $GLOBALS['prevTime']), 0);
          $GLOBALS['prevTime'] = microtime(true);
          $GLOBALS['prevSize'] = $downloaded;
          
          if($GLOBALS['averageSpeed'] != 0){
            $GLOBALS['timeRemaining'] = abs(round(($downloaded - $downloadSize) / $GLOBALS['averageSpeed'], 0));
          }else{
            $GLOBALS['timeRemaining'] = 0;
          }
        }
        $dInfo["eta"] = $GLOBALS['timeRemaining'];
        $dInfo["speed"] = $GLOBALS['currentSpeed'];
        saveData(array(
          $dName => $dInfo
        ));
      },
      CURLOPT_FILE => $savePathFP,
      CURLOPT_WRITEFUNCTION => function($ch, $data) use ($dName, $savePath, &$savePathFP) {
        if(isset($GLOBALS["curlCancel$dName"])){
          return 0;
        }
        /**
         * Live write to file
         */
        fseek($savePathFP, filesize($savePath));
        $len = fwrite($savePathFP, $data);
        
        return $len; //return the exact length
      }
    );
    
    if(file_exists($savePath)){
      $from = filesize($savePath);
      $responseHeaders = @get_headers($url, 1);
      
      if(isset($responseHeaders['Content-Length']) && $responseHeaders['Content-Length'] != $from){
        $GLOBALS['alreadyDownloaded'] = $from;
        $curlOptions[CURLOPT_RANGE] = $from . "-" . $responseHeaders['Content-Length'];
      }else{
        /**
         * File already fully downloaded, so skip
         */
        continue;
      }
    }
    
    $request = new \MultiRequest\Request($url);
    $request->addCurlOptions($curlOptions);
    $request->onFailed(function($this, $Exception, \MultiRequest\Handler $handler) use($dName, $dInfo){
      saveData(array(
        $dName => array(
          "error" => $Exception->getMessage(),
          /**
           * On Error, the percentage is 100 because request completed
           */
          "percentage" => "100"
        )
      ));
    });
    $mrHandler->pushRequestToQueue($request);
  }
  $mrHandler->start();
}
