lobby.load(function(){
  var checkDownloadStatus = setInterval(function(){
    lobby.ajax("contents/apps/downloader/module/status", {}, function(r){
      if(r === "completed"){
        clearInterval(checkDownloadStatus);
      }else{
        
      }
    });
  });
  
  if(sessionStorage.getItem("app-downloader-init")){
    
  }
});
