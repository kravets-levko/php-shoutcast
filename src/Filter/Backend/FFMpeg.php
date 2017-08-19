<?php

namespace Shoutcast\Filter\Backend;

use Shoutcast\Interfaces\Connector\IOutputMetadataSource;
use Shoutcast\Interfaces\Stream\IMetadataSource;

class FFMpeg extends External implements IOutputMetadataSource, IMetadataSource {

  protected $metadata = [];

  protected function readMetadata($pipe): array {
    $result = [];
    $start = false;
    while ($this -> isProcessAlive()) {
      $line = fgets($pipe, 2048);
      if ($line === false) continue;
      $line = trim($line);
      if (strtolower(substr($line, 0, 8)) == 'output #') break;

      if (!$start) {
        if (strtolower($line) == 'metadata:') $start = true;
      } else {
        if (($colon = strpos($line, ':')) !== false) {
          $name = trim(substr($line, 0, $colon));
          if (strtolower(substr($name, 0, 8)) == 'stream #') break;
          if (strtolower($name) != 'duration') {
            $value = trim(substr($line, $colon + 1));
            $result[$name] = $value;
          } else {
            $value = trim(substr($line, $colon + 1));
            if (($colon = strpos($value, ',')) !== false) {
              $value = trim(substr($value, 0, $colon));
            }
            $result['duration'] = (int)time_to_seconds($value);
          }
        }
      }
    }
    return $result;
  }

  protected function processStarted() {
    $pipe = $this -> getPipe(self::STDERR);
    if ($pipe) {
      $this -> metadata = $this -> readMetadata($pipe);
    }
    parent::processStarted();
  }

  public function getOutputMetadataSource(): IMetadataSource {
    return $this;
  }

  public function getMetadata(): array {
    return $this -> metadata;
  }

  public function getArtist(): string {
    $metadata = $this -> getMetadata();
    return $metadata['artist'];
  }

  public function getTitle(): string {
    $metadata = $this -> getMetadata();
    return $metadata['title'];
  }

  public function getDuration(): int {
    $metadata = $this -> getMetadata();
    return $metadata['duration'];
  }

}
