<?php

namespace Shoutcast\Filter\Playlist\Loader;

class Inline extends Blank {

  public function getItems(): ?array {
    return @$this -> config['items'];
  }

}
