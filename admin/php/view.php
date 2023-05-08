<?php
use function LOCALIZATION\L;
?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex,nofollow" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>File Exchange</title>
  <style>
    html, body {
      margin: 0;
    }
    body {
      padding: 20pt;
      box-sizing: border-box;
      width: 100%;
    }
    .directory-config div {
      display: inline-block;
      margin: 5pt 0;
      box-sizing: border-box;
      --label-width: 300pt;
    }
    .directory-config div:nth-child(odd) {
      width: var(--label-width);
    }
    .directory-config div:nth-child(even) {
      min-width: var(--label-width);
      width: calc(100% - var(--label-width) - 10pt);
    }
    .directory-config div input[type="text"] {
      width: 100%;
    }
  </style>
</head>
<body>
    <form action="./?<?=add_url_params(['action' => 'save'])?>" method="post" class="directory-config">
      <div></div>
      <div><i><?=$id?></i></div>
      <div><?=L('upload_directory')?></div>
      <div>
        <input type="text" name="dir" autofocus>
      </div>
      <div><?=L('allowed_file_extensions')?></div>
      <div><input type="text" name="allowed_file_extensions" value="<?=implode(', ', ['.zip','.png','.jpg'])?>"></div>
      <div><?=L('prohibited_file_extensions')?></div>
      <div><input type="text" name="prohibited_file_extensions"></div>
      <div><?=L('max_file_size')?></div>
      <div><input type="text" name="max_file_size" value="<?=exact_file_size_str(try_get_server_upload_max_filesize())?>"></div>
      <div><?=L('disk_quota')?></div>
      <div><input type="text" name="disk_quota" value="100M"></div>
      <input type="hidden" name="id" value="<?=$id?>">
      <input type="hidden" name="action" value="save">
      <div></div>
      <div>
        <input type="submit" value="<?=L('save')?>">
      </div>
    </form>
    <?php

  function exact_file_size_str($num_bytes)
  {
    foreach (['', 'K', 'M', 'G'] as $unit) {
      if ($num_bytes % 1024 !== 0) {
        return $num_bytes . $unit;
      }
      $num_bytes /= 1024;
    }
    return $num_bytes . 'T';
  }
  ?>

  <div><b><?=L('new_directory')?></b></div>
  <div>
    <?=dir_config()?>
  </div>
</body>
</html>