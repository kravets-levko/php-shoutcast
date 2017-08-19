<?php

namespace Shoutcast;

abstract class Filter {

  protected $config = [];

  protected function afterConstruction() {
  }

  public function __construct(array $config = []) {
    $this -> config = $config;
    $this -> afterConstruction();
  }

}
