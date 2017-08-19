<?php

function seconds_to_time(float $seconds): string {
  $result = [];
  $value = (int)$seconds;
  $tail = substr(number_format(($seconds - $value), 2), 2);
  $tail = rtrim($tail, '0');
  if ($tail != '') $tail = '.' . $tail;

  $result[] = str_pad($value % 60, 2, '0', STR_PAD_LEFT) . $tail;
  $value = (int)($value / 60);

  $result[] = str_pad($value % 60, 2, '0', STR_PAD_LEFT);
  $value = (int)($value / 60);

  $result[] = str_pad($value, 2, '0', STR_PAD_LEFT);
  return implode(':', array_reverse($result));
}

function time_to_seconds(string $time): float {
  $time = explode(':', $time);
  $seconds = 0;
  if (count($time) > 0) $seconds += (float)array_pop($time);
  if (count($time) > 0) $seconds += 60 * (float)array_pop($time);
  if (count($time) > 0) $seconds += 60 * 60 * (float)array_pop($time);
  return $seconds;
}

function parse_metadata_string(?string $value): array {
  $result = [];
  $value = trim($value);
  if ($value != '') {
    $value = explode('-', $value, 2);
    $value = array_filter(array_map('trim', $value), 'strlen');
    if (count($value) == 2) {
      $result['artist'] = reset($value);
      $result['title'] = end($value);
    } elseif (count($value) == 1) {
      $result['title'] = end($value);
    }
  }
  return $result;
}
