<?php
namespace Lobby\Module;

use \Lobby\UI\Panel;
use \H;

class app_downloader extends \Lobby\Module {

  public function init(){
    if($this->app->isDownloadRunning()){
      $this->app->addNotifyItem("download_info", array(
        "contents" =>  "Download Running"
      ));
      $this->addScript("notify-download-status.js");
    }else{
      $this->app->removeNotifyItem("download_info");
    }
    
    if(H::i("cx74e9c6a45") === "contents/apps/downloader/module/status"){
      var_dump("hello");
    }
  }

}
