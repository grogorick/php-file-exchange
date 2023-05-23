<?php
use function LOCALIZATION\L;

if (isset($_GET['action-json'])) {
  header('Content-Type: application/json; charset=utf-8');

  switch ($_GET['action-json']) {
    case 'ls':
      {
        $ls = scandir(from_url($_GET['dir']));
        if ($ls === false)
          echo 'false';
        else
          echo json_encode(
            array_values(array_filter(
              $ls,
              fn($dir) => !str_contains('.@', $dir[0])
            ))
          );
      }
      break;

    case 'check':
      {
        echo json_encode(check_dir(from_url($_GET['dir'])));
      }
      break;
  }

  exit();
}

if (isset($_GET['action'])) {
  switch ($_GET['action']) {
    case 'save':
      {
        $id = $_POST['id'] ?: strval(time());
        $dir = trim($_POST['dir']);
        if (mb_substr($dir, -1) !== '/')
          $dir .= '/';
        $conf = [
          'alias' => trim($_POST['alias']),
          'dir' => $dir,
          'allowed_ext' => POST_parse_comma_separated_list('allowed_file_extensions'),
          'prohibited_ext' => POST_parse_comma_separated_list('prohibited_file_extensions'),
          'max_file_size' => trim($_POST['max_file_size']),
          'disk_quota' => trim($_POST['disk_quota']),
          'password' => $_POST['password']
        ];
        $err = check_dir($conf['dir']);
        if ($err !== true) {
          set_error(L(...$err));
          break;
        }
        if ($conf['alias']) {
          if (!preg_match('/^' . ALIAS_PATTERN . '$/', $conf['alias'])) {
            $conf['alias'] = '';
            set_error(L('invalid_url_alias'));
          }
          else
            foreach (DirectoryConfig::dict() as $other_id => $other_conf) {
              if (strval($other_id) !== $id && $other_conf['alias'] === $conf['alias']) {
                $conf['alias'] = '';
                set_error(L('url_alias_already_exists', $other_conf['alias']), $id);
                break;
              }
            }
        }
        if (!is_null($conf['password'])) {
          $old_conf = DirectoryConfig::get($id);
          if ($conf['password'] !== $old_conf['password'])
            $conf['password'] = encrypt_password($conf['password']);
        }
        DirectoryConfig::set($id, $conf);
      }
      break;

    case 'delete':
      {
        DirectoryConfig::delete($_GET['id']);
        unset($_GET['id']);
      }
      break;

    case 'logout':
      {
        Auth::logout();
      }
      break;
  }

  unset($_GET['action']);
  header('Location: ./?' . http_build_query($_GET));
  exit();
}

function set_error($error, $conf_id = null)
{
  $_SESSION['error-' . ($conf_id ?? $_POST['id'])] = $error;
}

function check_dir($path)
{
  if (!is_dir($path)) {
    return ['not_a_directory', $path];
  }
  if (!is_readable($path)) {
    return ['no_read_access', $path];
  }
  if (!is_writable($path)) {
    return ['no_write_access', $path];
  }
  return true;
}

function POST_parse_comma_separated_list($str)
{
  return array_filter(array_map(fn($val) => trim($val), explode(',', $_POST[$str])));
}
