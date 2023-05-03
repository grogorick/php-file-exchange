<?php
use function LOCALIZATION\L;

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