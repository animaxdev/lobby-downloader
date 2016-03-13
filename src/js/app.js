$.extend(lobby.app, {
  refresh: function(){
    
  }
});

lobby.load(function(){
  
  $("#newDownload").live("click", function(){
    $("#newDownloadDialog").dialog({
      width: 500
    });
  });
  
  $("#newDownloadDialog form").live("submit", function(e){
    e.preventDefault();
    lobby.app.ajax("add-download.php", $(this).serializeForm(), function(d){
      if(d != "bad"){
        lobby.app.refresh();
      }
    });
  });
  
});
