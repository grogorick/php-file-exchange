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