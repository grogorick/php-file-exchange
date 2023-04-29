<?php

function file_size($file_name)
{
  $num_bytes = filesize(DIR . $file_name);
  foreach (['', 'K', 'M'] as $prefix) {
    if ($num_bytes < 1024) {
      return $num_bytes . ' ' . $prefix . 'B';
    }
    $num_bytes = $num_bytes >> 10;
  }
  return $num_bytes . ' GB';
}