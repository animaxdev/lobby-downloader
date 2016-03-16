$.extend(lobby.app, {
  refresh: function(){
    lobby.app.ajax("downloads.php", {}, function(d){
      $(".workspace #downloads").replaceWith(d);
    });
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
    lobby.app.ajax("new-download.php", $(this).serializeArray(), function(d){
      if(d != "bad"){
        lobby.app.refresh();
      }
    });
  });
  
});
