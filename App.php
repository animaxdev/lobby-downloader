<?php
namespace Lobby\App;

class downloader extends \Lobby\App {

  public function page($p){
    require_once APP_DIR . "/src/lib/vendor/autoload.php";
    return "auto";
  }
  
  /**
   * Add a download
   */
  public function addDownload($url){
    $dName = md5($url);
    \H::saveJSONData($dName, array(
      "url" => $url
    ));
    \H::saveJSONData("downloads", array($dName));
    $this->download($dName);
  }
  
  public function download($callback){
    $curlOptions = array(
      CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
      CURLOPT_NOPROGRESS => false
      CURLOPT_PROGRESSFUNCTION => function($progress){
        
      }
    );
    $mrHandler = new \MultiRequest\Handler();
    $mrHandler->setConnectionsLimit(1000);
    $mrHandler->onRequestComplete($callback);
    $mrHandler->requestsDefaults()->addCurlOptions($curlOptions);
    
    $Session = new \MultiRequest\Session($mrHandler, '/tmp');
    $Session->start();
    
    foreach($urls as $url) {
      $request = new \MultiRequest\Request($url);
      $request->addCurlOptions($curlOptions);
      $Session->request($request);
      //$mrHandler->pushRequestToQueue($request);
    }
    $mrHandler->start();
  }

}
