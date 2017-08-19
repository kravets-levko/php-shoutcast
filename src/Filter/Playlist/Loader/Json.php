<?php

namespace Shoutcast\Filter\Playlist\Loader;

class Json extends Blank {

  public function getItems(): ?array {
    $filename = @$this -> config['filename'];
    return json_decode(file_get_contents($filename), true);
  }

}
