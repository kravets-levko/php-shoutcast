<?php

namespace Shoutcast\Filter\Playlist\Loader;

class M3U extends Blank {

  protected function parsePlaylist($filename, &$result) {
    $lines = file($filename);
    $metadata = [];
    foreach ($lines as $line) {
      $line = rtrim(ltrim($line), "\r\n\0");
      if ($line == '') continue;
      if ($line{0} == '#') {
        if (strtoupper(substr($line, 0, 8)) == '#EXTINF:') {
          $metadata = [];
          $line = explode(',', substr($line, 8), 2);
          if (count($line) == 2) {
            $metadata = parse_metadata_string(end($line));
            $metadata['duration'] = (int)reset($line);
            if ($metadata['duration'] <= 0) $metadata['duration'] = -1;
          }
        } else {
          $metadata = [];
        }
      } else {
        $metadata['filename'] = $line;
        $result[] = $metadata;
        $metadata = [];
      }
    }
  }

  public function getItems(): ?array {
    $filename = @$this -> config['filename'];
    if (is_string($filename)) $filename = [$filename];
    if (!is_array($filename)) $filename = [];
    $result = [];
    foreach ($filename as $item) {
      $this -> parsePlaylist($item, $result);
    }
    return $result;
  }

}
