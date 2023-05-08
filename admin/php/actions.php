<?php
use function LOCALIZATION\L;

if (isset($_GET['action'])) {
  switch ($_GET['action']) {
    case 'save':
      {
        $dir = trim($_POST['dir']);
        if (mb_substr($dir, -1) !== '/')
          $dir .= '/';
        $conf = [
          'dir' => $dir,
          'allowed_ext' => parse_comma_separated_list('allowed_file_extensions'),
          'prohibited_ext' => parse_comma_separated_list('prohibited_file_extensions'),
          'max_file_size' => trim($_POST['max_file_size']),
          'disk_quota' => trim($_POST['disk_quota'])
        ];
        $err = check_dir($conf['dir']);
        if ($err !== true) {
          echo L(...$err);
          break;
        }
        DirectoryConfig::set($_POST['id'] ?: time(), $conf);
      }
      break;
  }

  unset($_GET['action']);
  header('Location: ./?' . http_build_query($_GET));
  exit();
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

function parse_comma_separated_list($str)
{
  return array_map(fn($val) => trim($val), explode(',', $_POST[$str]));
}
