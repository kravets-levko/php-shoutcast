<?php

namespace Shoutcast\Filter\Playlist\Loader;

class Blank {

  protected $config = [];

  public function __construct(array $config = []) {
    $this -> config = $config;
  }

  public function getItems(): ?array {
    return [];
  }

}
