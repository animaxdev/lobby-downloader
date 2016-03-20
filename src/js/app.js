$.extend(lobby.app, {

  downloadStatusCheck: "",
  
  init: function(){
    lobby.app.ajax("init.php", {}, function(r){
      r = JSON.parse(r);
      if(r.status === "started"){
        $.each(r.active, function(i, dName){
          $(".workspace #downloads .card[data-id="+ dName +"]").attr("data-active", "1");
        });
      }
      if(r.status === "started" || r.status === "running"){
        clearInterval(lobby.app.downloadStatusCheck);
        lobby.app.downloadStatusCheck = setInterval(function(){
          lobby.app.refresh();
        }, 1000);
      }
    });
  },
  
  refresh: function(){
    lobby.app.ajax("downloads.php", {}, function(d){
      if($(".workspace #downloads .card[data-active]").length == 0){
        $(".workspace #downloads").replaceWith(d);
        clearInterval(lobby.app.downloadStatusCheck);
      }else{
        $(d).find(".card").each(function(){
          id = $(this).data("id");
          cur = $(".workspace #downloads .card[data-id="+ id +"]");
          if(parseFloat($(this).find(".determinate").css("width")) != parseFloat(cur.find(".determinate")[0].style.width)){
            cur.find(".determinate").css("width", $(this).find(".determinate").css("width"));
            cur.find(".download-info").html($(this).find(".download-info").html());
          }
          if($(this).find(".determinate").css("width") == "100%"){
            cur.removeAttr("data-active");
          }
        });
      }
    });
  }
  
});

lobby.load(function(){
  lobby.app.init();
  
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
        lobby.app.init();
        $("#newDownloadDialog").dialog("close");
      }else if(d == "urlNotFound"){
        alert("URL Not Found");
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
        t.fadeOut(500, function(){
          t.remove();
          lobby.app.refresh();
        });
      }
    });
  });
  
  $("#downloads .card #reDownload").live("click", function(){
    dName = $(this).parents(".card").data("id");
    lobby.app.ajax("retry-download.php", {downloadID: dName}, function(){
      lobby.app.init();
    });
  });
  
  $(".card #pauseDownload").live("click", function(){
    p = $(this).parents(".card");
    dName = p.data("id");
    t = $(this);
    lobby.app.ajax("pause-download.php", {downloadID: dName}, function(){
      t.hide();
      p.find("#resumeDownload").show();
      p.removeAttr("data-active");
    });
  });
  
  $(".card #resumeDownload").live("click", function(){
    p = $(this).parents(".card");
    dName = p.data("id");
    t = $(this);
    lobby.app.ajax("pause-download.php", {downloadID: dName, resume: "1"}, function(){
      t.hide();
      p.find("#pauseDownload").show();
      p.attr("data-active", 1);
      lobby.app.init();
    });
  });
  
});
