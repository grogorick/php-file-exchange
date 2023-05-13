<?php
use function LOCALIZATION\L;
?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex,nofollow" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>File Exchange Admin</title>
  <link rel="icon" type="image/png" href="../favicon.png" />
  <style>
    html, body {
      margin: 0;
    }
    body {
      padding: 20pt;
      box-sizing: border-box;
      width: 100%;
    }
    #logout {
      margin-bottom: 20pt;
      text-align: right;
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
    .directory-config .error {
      color: red;
    }
  </style>
</head>
<body>
  <div id="logout"><a href="./?<?=add_url_params(['action' => 'logout'])?>">Logout</a></div>

  <?php
  function dir_config($id = null, $conf = null)
  {
    ?>

    <form action="./?<?=add_url_params(['action' => 'save'])?>" <?=is_null($id) ? 'id="edit-' . $id . '"' : ''?> method="post" class="directory-config">
      <div></div>
      <div><i><?=$id?></i></div>
      <div><?=L('url_alias')?></div>
      <div><input type="text" name="alias" pattern="<?=ALIAS_PATTERN?>" value="<?=$conf['alias']?>" title="e.g. my-catchy-url"></div>
      <div><?=L('upload_directory')?></div>
      <div>
        <input type="text" name="dir" value="<?=$conf['dir']?>" autofocus autocomplete="off" list="relative_directories_<?=$id?>">
        <datalist id="relative_directories_<?=$id?>"></datalist>
        <span class="dir-check"></span>
      </div>
      <div><?=L('allowed_file_extensions')?></div>
      <div><input type="text" name="allowed_file_extensions" value="<?=implode(', ', $conf['allowed_ext'] ?? [])?>" title="e.g. .zip, .png, .jpg"></div>
      <div><?=L('prohibited_file_extensions')?></div>
      <div><input type="text" name="prohibited_file_extensions" value="<?=implode(', ', $conf['prohibited_ext'] ?? [])?>" title="e.g. .exe, .sh, .bat, .php, .html"></div>
      <div><?=L('max_file_size')?></div>
      <div><input type="text" name="max_file_size" value="<?=$conf['max_file_size']?>" title="server max.: <?=exact_file_size_str(SERVER_UPLOAD_MAX_FILESIZE)?>"></div>
      <div><?=L('disk_quota')?></div>
      <div><input type="text" name="disk_quota" value="<?=$conf['disk_quota']?>" title="e.g. 1G"></div>
      <input type="hidden" name="id" value="<?=$id?>">
      <input type="hidden" name="action" value="save">
      <div></div>
      <div>
        <input type="submit" value="<?=L('save')?>">
        <?php if (!is_null($id)) { ?>

        <a href="./?<?=add_url_params(['action' => 'delete', 'id' => $id])?>"><button type="button"><?=L('delete')?></button></a>
        <?php } ?>

      </div>
      <div></div>
      <div class="error"><?=$_SESSION['error-' . $id]?></div>
    </form>
    <?php
    unset($_SESSION['error-' . $id]);
  }

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
  <hr>
  <div><b><?=L('edit_directories')?></b></div>
  <div>
    <?php
    foreach (DirectoryConfig::dict() as $id => $conf)
      dir_config($id, $conf);
    ?>
  </div>
  <?=LOCALIZATION\INIT_JS()?>
  <script src="../js/utils.js"></script>
  <script src="js/script.js"></script>
</body>
</html>