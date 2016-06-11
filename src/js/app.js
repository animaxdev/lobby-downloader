$.extend(lobby.app, {

  downloadStatusCheck: "",
  
  init: function(){
    lobby.app.ajax("init.php", {}, function(r){
      r = JSON.parse(r);
      if(r.status === "started"){
        $.each(r.active, function(i, dName){
          $("#workspace #downloads .card[data-id="+ dName +"]").attr("data-active", "1");
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
  
  /**
   * full = 0 Dynamically increase progress
   * full = 1 Replace #downloads entirely
   */
  refresh: function(full){
    lobby.app.ajax("downloads.php", {}, function(d){
      if(full == 1){
        $("#workspace #downloads").replaceWith(d);
        lobby.app.init();
      }else{
        $(d).find(".card").each(function(){
          id = $(this).data("id");
          cur = $("#workspace #downloads .card[data-id="+ id +"]");
          
          curPercentage = parseFloat(cur.find(".determinate")[0].style.width);
          newPercentage = parseFloat($(this).find(".determinate").css("width"));
          
          if(newPercentage == "100" && curPercentage != "100"){
            lobby.app.refresh(1);
          }else if(newPercentage != curPercentage){
            cur.find(".determinate").css("width", $(this).find(".determinate").css("width"));
            cur.find(".download-info").html($(this).find(".download-info").html());
            cur.find(".controls").html($(this).find(".controls").html());
          }
        });
      }
      if($("#workspace #downloads .card[data-active]").length == 0){
        clearInterval(lobby.app.downloadStatusCheck);
      }
    });
  },
  
  toggleView: function(){
    
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
    if($("#workspace #downloads .card[data-notresumable][data-active]").length != 0){
      $("<h4>Can't Add Download</h4><div>Downloads that are un-resumable are running right now. These download(s) can't be resumed. Until all un-resumable downloads are finished, no new downloads can be added.</div>")
    }else{
      lobby.app.ajax("new-download.php", $(this).serializeArray(), function(d){
        if(d != "bad"){
          $("#newDownloadDialog").dialog("close");
          lobby.app.refresh(1);
        }else if(d == "urlNotFound"){
          $("URL Not Found").dialog();
        }
      });
    }
  });
  
  $("#newDownloadDialog #chooseDLoc").live("click", function() {
    default_dir = $("#newDownloadDialog #dLoc").val();
    lobby.mod.FilePicker(default_dir, function(result) {
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
          lobby.app.refresh(1);
        });
      }
    });
  });
  
  $("#downloads .card #reDownload").live("click", function(){
    dName = $(this).parents(".card").data("id");
    lobby.app.ajax("restart-download.php", {downloadID: dName}, function(){
      lobby.app.refresh(1);
    });
  });
  
  $(".card #pauseDownload").live("click", function(){
    t = $(this);
    p = t.parents(".card");
    dName = p.data("id");
    lobby.app.ajax("pause-download.php", {downloadID: dName}, function(){
      /*t.hide();
      p.find("#resumeDownload").css('display', 'inline-block');
      p.removeAttr("data-active");
      lobby.app.init();*/
      lobby.app.refresh(1);
    });
  });
  
  $(".card #resumeDownload").live("click", function(){
    t = $(this);
    p = t.parents(".card");
    dName = p.data("id");
    lobby.app.ajax("pause-download.php", {downloadID: dName, resume: "1"}, function(){
      /*t.hide();
      p.find("#pauseDownload").css('display', 'inline-block');
      p.attr("data-active", 1);*/
      lobby.app.refresh(1);
    });
  });
  
});
