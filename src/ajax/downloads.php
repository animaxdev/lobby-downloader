<div id="downloads">
  <?php
  $ds = \H::getJSONData("downloads");
  if($ds === null){
    ser("No Downloads", "Why don't you download some stuff ?");
  }else{
    echo "<div class='card'>";
      foreach($ds as $d){
        $dInfo = \H::getJSONData($d);
      ?>
        <div class='card-content'>
          <span class="card-title"><?php echo $dInfo['url'];?></span>
          <p>
            <div class="progress">
              <div class="determinate" style="width: <?php echo $dInfo['downloaded'];?>"></div>
            </div>
          </p>
        </div>
      <?php
      }
    echo "</div>";
  }
  ?>
</div>
