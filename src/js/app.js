$.extend(lobby.app, {
  
  init: function(){
    lobby.app.ajax("init.php", {}, function(){
      
    });
  },
  
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
  
  $("#newDownloadDialog #chooseDLoc").live("click", function() {
    lobby.mod.FilePicker("/", function(result) {
      $("#newDownloadDialog #dLoc").val(result.dir);
      lobby.app.save("downloadsDir", result.dir);
    });
  });
  
  $(".card #removeDownload").live("click", function(){
    t = $(this).parents(".card");
    n = t.data("id");
    lobby.app.ajax("remove-download.php", {downloadName: n}, function(r){
      if(r !== "bad"){
        t.fadeOut(1000, function(){
          t.remove();
        });
      }
    });
  });
  
  lobby.app.init();
});
