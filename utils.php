<?php

function file_size($file_name)
{
  return file_size_str(filesize(DIR . $file_name));
}

function file_size_str($num_bytes)
{
  foreach (['', 'K', 'M'] as $prefix) {
    if ($num_bytes < 1024) {
      return $num_bytes . ' ' . $prefix . 'B';
    }
    $num_bytes = $num_bytes >> 10;
  }
  return $num_bytes . ' GB';
}

function parse_file_size($file_size)
{
  if (function_exists('ini_parse_quantity'))
    return ini_parse_quantity($file_size);

  if (preg_match('/([0-9]+)([.][0-9]*)?([KkMmGg])/', $file_size, $match)) {
    [$_, $num, $_, $mod] = $match;
    $num = intval($num);
    switch ($mod) {
      case 'G':
      case 'g': $num *= 1024;
      case 'M':
      case 'm': $num *= 1024;
      case 'K':
      case 'k': $num *= 1024;
      default:
    }
    return $num;
  }
  else
    return intval($file_size);
}