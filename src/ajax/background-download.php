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
    
    $server_output = curl_exec ($ch);
    curl_close ($ch);
  }
  
  require_once __DIR__ . "/../lib/vendor/autoload.php";
  
  $mrHandler = new \MultiRequest\Handler();
  $mrHandler->setConnectionsLimit(1000);
  
  $GLOBALS['startTime'] = $GLOBALS['prevTime'] = microtime(true);
  $GLOBALS['prevSize'] = $GLOBALS['currentSpeed'] = $GLOBALS['timeRemaining'] = 0;
  foreach($ds as $dName => $dInfo) {
    $url = $dInfo['url'];
    $savePath = $dInfo['downloadDir'] . DIRECTORY_SEPARATOR . $dInfo['fileName'];
    
    $savePathFP = fopen($savePath, "a");

    $curlOptions = array(
      CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
      CURLOPT_NOPROGRESS => false,
      CURLOPT_PROGRESSFUNCTION => function($resource, $download_size, $downloaded, $upload_size, $uploaded = "") use($dName) {
        /**
         * On new versions of cURL, $resource parameter is not passed
         * So, swap vars if it doesn't exist
         */
        if(!is_resource($resource)){
          $uploaded = $upload_size;
          $upload_size = $downloaded;
          $downloaded = $download_size;
          $download_size = $resource;
        }
        if($downloaded > 0 && $download_size > 0){
          $percentage = ($downloaded / $download_size) * 100;
        }else{
          $percentage = 0;
        }
        
        $dInfo = array(
          "downloaded" => $downloaded,
          "error" => "0",
          "percentage" => $percentage,
          "size" => $download_size
        );
        
        if($GLOBALS['prevTime'] < strtotime("-1 second")){
          /**
           * Calculate Speed
           */
          $GLOBALS['averageSpeed'] = $downloaded / (microtime(true) - $GLOBALS['startTime']);
          
          $GLOBALS['currentSpeed'] = round(($downloaded - $GLOBALS['prevSize']) / (microtime(true) - $GLOBALS['prevTime']), 0);
          $GLOBALS['prevTime'] = microtime(true);
          $GLOBALS['prevSize'] = $downloaded;
          
          if($GLOBALS['averageSpeed'] != 0){
            $GLOBALS['timeRemaining'] = abs(round(($downloaded - $download_size) / $GLOBALS['averageSpeed'], 0));
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
      CURLOPT_FILE => $savePathFP
    );
    
    if(file_exists($savePath)){
      $from = filesize($savePath);
      $curlOptions[CURLOPT_RANGE] = $from . "-";
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
