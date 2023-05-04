<?php

function file_size($file_name)
{
  return file_size_str(filesize(DIR . $file_name));
}

function file_size_str($num_bytes)
{
  foreach (['', 'K', 'M', 'G'] as $prefix) {
    if ($num_bytes < 1024) {
      return round($num_bytes, 2) . ' ' . $prefix . 'B';
    }
    $num_bytes /= 1024;
  }
  return $num_bytes . ' TB';
}

function parse_file_size($file_size)
{
  if (is_int($file_size))
    return $file_size;

  if (function_exists('ini_parse_quantity'))
    return ini_parse_quantity($file_size);

  if (preg_match('/([0-9]+)([.][0-9]*)?([KMGT])/', strtoupper($file_size), $match)) {
    [$_, $num, $_, $mod] = $match;
    $num = intval($num);
    switch ($mod) {
      case 'T': $num *= 1024;
      case 'G': $num *= 1024;
      case 'M': $num *= 1024;
      case 'K': $num *= 1024;
      default:
    }
    return $num;
  }
  else
    return intval($file_size);
}

function used_disk_space()
{
  $size = 0;
  foreach (glob(DIR . '*') as $file)
    if (is_file($file))
      $size += filesize($file);
  return $size;
}


function file_time($file_name)
{
  return file_time_str(filemtime(DIR . $file_name));
}

function file_time_str($timestamp)
{
  $locale = LOCALIZATION\GET_LOCALE();
  if (function_exists('datefmt_create')) {
    $fmt = datefmt_create(
      $locale,
      IntlDateFormatter::FULL,
      IntlDateFormatter::MEDIUM,
      null, // timezone
      IntlDateFormatter::GREGORIAN,
      'EEE dd MMM yyyy HH:mm:ss'
    );
    return datefmt_format($fmt, $timestamp);
  }
  else {
    $locales = [];
    foreach ([str_replace('-', '_', $locale), str_replace('_', '-', $locale)] as $loc) {
      $locales = array_merge($locales, [$loc, $loc.'.utf8', $loc.'.UTF8', $loc.'.utf-8', $loc.'.UTF-8', $loc.'.iso-8859-1', $loc.'.ISO-8859-1', $loc.'.CP1251', $loc.'.1252', $loc.'@euro']);
    }
    if (setlocale(LC_TIME, ...$locales)) {
      return strftime('%a %d %b %Y %H:%M:%S', $timestamp);
    }
    else {
      return date("d.m.Y H:i:s", $timestamp);
    }
  }
}


function to_url($file_name)
{
  return urlencode(base64_encode($file_name));
}

function from_url($param)
{
  return base64_decode(urldecode($param));
}

function add_url_params($param_value_array)
{
  $arr = $_GET;
  foreach ($param_value_array as $param => $value)
    $arr[$param] = $value;
  return http_build_query($arr);
}