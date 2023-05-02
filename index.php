<?php
require('localization/localization.php');
LOCALIZATION\INIT_FROM_FILE('localization.yaml');
LOCALIZATION\SET_LOCALE($_GET['lang'] ?? null);
use function LOCALIZATION\L;

require('utils.php');

$dir = $_GET['dir'] ?? './FILES/';
if (!in_array(mb_substr($dir, -1), ['\\', '/']))
  $dir += '/';
define('DIR', $dir);

$allowed_file_extensions = []; // ['.jpg', '.png', '.zip'];
$prohibited_file_extensions = ['.htm', '.pdf'];
$max_file_size = null; // 1024 * 1024;

$max_file_size = $max_file_size ?: parse_file_size(ini_get('upload_max_filesize'));

if (!is_dir(DIR)) {
  if (file_exists((DIR)))
    die(L('precheck_path_is_file', DIR));
  elseif (!mkdir(DIR, 0777, true))
    die(L('precheck_mkdir_failed', DIR));
}

function action_response($data)
{
  if (isset($_GET['no-js'])) {
    unset($_GET['action']);
    unset($_GET['no-js']);
    header('Location: ./?' . http_build_query($_GET));
  }
  else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
  }
  exit();
}

switch ($_GET['action']) {
  case 'download':
    {
      $file_name = from_url($_GET['file']);
      if (strpos($file_name, '/') !== false || strpos($file_name, '\\') !== false || !file_exists(DIR . $file_name))
        die(L('file_not_found'));

      header('Content-Type: application/octet-stream');
      header('Content-Transfer-Encoding: Binary');
      header('Content-disposition: attachment; filename="' . $file_name . '"');
      readfile(DIR . $file_name);
      exit();
    }

  case 'upload':
    {
      $response = [];

      $err = error_get_last();
      if (!is_null($err)) {
        $err_msg = $err['message'];
        if (str_starts_with($err_msg, 'POST Content-Length'))
          action_response([null, null, ['upload_failed_server_upload_size']]);

        action_response([null, null, [$err_msg]]); // any other error
      }

      foreach ($_FILES['files']['error'] as $i => $error_code) {
        $file_name = $_FILES['files']['name'][$i];

        if ($error_code !== UPLOAD_ERR_OK) {
          $response[] = [$i, null, ['upload_failed_error_code', $error_code]];
          continue;
        }

        $file_ext = strtolower(substr($file_name, strrpos($file_name, '.')));
        if (!empty($allowed_file_extensions) && !in_array($file_ext, $allowed_file_extensions)) {
          $response[] = [$i, null, ['upload_failed_file_type', $file_ext]];
          continue;
        }

        if (in_array($file_ext, $prohibited_file_extensions)) {
          $response[] = [$i, null, ['upload_failed_file_type', $file_ext]];
          continue;
        }

        $file_size = $_FILES['files']['size'][$i];
        if ($file_size > $max_file_size) {
          $response[] = [$i, null, ['upload_failed_file_size', file_size_str($file_size), file_size_str($max_file_size)]];
          continue;
        }

        $file_tmp = $_FILES['files']['tmp_name'][$i];
        $upload_successful = move_uploaded_file($file_tmp, DIR . $file_name);
        if (!$upload_successful) {
          $response[] = [$i, null, ['upload_failed_move_failed']];
          continue;
        }

        $response[] = [
            $i,
            [
              'url_name' => to_url($file_name),
              'time' => file_time($file_name)
            ],
            [false]
          ];
      }
      action_response($response);
    }

  case 'delete':
    {
      if (isset($_POST['file']))
        $files = [$_POST['file']];
      else
        $files = $_POST['files'];

      $response = [];
      foreach ($files as $i => &$url_file_name) {
        $file_name = from_url($url_file_name);
        if (!file_exists(DIR . $file_name))
          $response[] = [$i, ['name' => $file_name], ['file_not_found']];
        else if (!unlink(DIR . $file_name))
          $response[] = [$i, ['name' => $file_name], ['file_could_not_be_deleted']];
        else
          $response[] = [$i, ['name' => $file_name], [false]];
      }
      action_response($response);
    }

  default:
}

?>
<!DOCTYPE html>
<html lang="de" xml:lang="de">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex,nofollow" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>File Exchange</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

  <div id="messages"></div>

  <?php
    $url_upload = add_url_params([
      'action' => 'upload',
      'no-js' => ''
    ]);
  ?>
  <form id="file-upload-form" action="./?<?=$url_upload?>" method="post" enctype="multipart/form-data">
    <div class="row">
      <input id="file-input" class="button" type="file" name="files[]" multiple>
      <label for="file-input" class="button hidden"><?=L('select_files')?></label>
      <input type="submit" class="button" value="<?=L('upload')?>">
    </div>
  </form>

  <div id="file-list">
    <?php
      function file_element($file_name = null)
      {
        $is_template = is_null($file_name);
        $url_download = add_url_params([
          'action' => 'download',
          'file' => is_null($file_name) ? '' : to_url($file_name)
        ]);
        $url_delete = add_url_params([
          'action' => 'delete',
          'no-js' => ''
        ]);
        ?>
            <div class="row item <?=$is_template ? 'hidden' : ''?>" <?=$is_template ? 'id="file-item-template"' : ''?>>
              <div class="file-name"><a class="download" download href="./?<?=$url_download?>"><?=$file_name?></a></div>
              <div class="file-details">
                <span class="file-size"><?=$is_template ? '' : file_size($file_name)?></span>
                <span class="file-time"><?=$is_template ? '' : file_time($file_name)?></span>
              </div>
              <div class="file-delete">
                <form action="./?<?=$url_delete?>" method="post">
                  <input type="hidden" name="file" value="<?=to_url($file_name)?>">
                  <input class="button" type="submit" value="X">
                </form>
              </div>
            </div>
        <?php
      }

      $files = scandir(DIR);
      foreach ($files as $file_name)
        if (is_file(DIR . $file_name))
          file_element($file_name);

      file_element();
    ?>

    <div class="row empty">
      <?=L('empty')?>
    </div>
  </div>

  <div id="drag-drop-indicator" class="hidden"><div><div><?=L('drop_files')?></div></div></div>

  <script>
    let allowedFileExtensions = [<?=implode(', ', array_map(fn($ext) => '\'' . $ext . '\'', $allowed_file_extensions))?>];
    let prohibitedFileExtensions = [<?=implode(', ', array_map(fn($ext) => '\'' . $ext . '\'', $prohibited_file_extensions))?>];
    let maxFileSize = <?=$max_file_size?>;
  </script>
  <?=LOCALIZATION\INIT_JS()?>
  <script src="js/script.js"></script>

</body>
</html>