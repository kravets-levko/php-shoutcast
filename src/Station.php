<?php

namespace Shoutcast;

require __DIR__ . '/utilities.php';

use Shoutcast\Graph\Player;

class Station {

  protected static $graphs = [
    'player' => Player::class,
  ];

  protected $config = [];

  protected function getStationInfoHeaders(array $config): array {
    $result = [];

    if (isset($config['name'])) {
      $result[] = "ICY-Name: {$config['name']}";
    }
    if (isset($config['description'])) {
      $result[] = "ICY-Description: {$config['description']}";
    }
    if (isset($config['url'])) {
      $result[] = "ICY-Url: {$config['url']}";
    }
    if (isset($config['genre'])) {
      $result[] = "ICY-Genre: {$config['genre']}";
    }

    if ($config['metadata']) {
      $result[] = "ICY-Metaint: {$config['metaint']}";
    }

    $isPublic = isset($config['public']) ? $config['public'] : false;
    $result[] = "ICY-Pub: " . ($isPublic ? '1' : '0');
    $result[] = "ICY-Private: " . ($isPublic ? '0' : '1');

    if (isset($config['notice'])) {
      $notice = [];
      if (is_string($config['notice'])) {
        $notice = [$config['notice']];
      } elseif (is_array($config['notice'])) {
        $notice = $config['notice'];
      }
      foreach (array_values($notice) as $index => $value) {
        $index += 1;
        $result[] = "ICY-Notice{$index}: {$value}";
      }
    }

    return $result;
  }

  protected function getAudioInfoHeaders(array $config): array {
    $result = [];

    if (isset($config['bitrate'])) {
      $result[] = "ICY-BR: {$config['bitrate']}";
    }
    $info = array_filter(array_intersect_key($config, [
      'bitrate' => true,
      'samplerate' => true,
      'channels' => true,
    ]));
    $info = implode(';', array_map(function($key, $value) {
      return "{$key}={$value}";
    }, array_keys($info), array_values($info)));
    if ($info != '') {
      $result[] = "ICE-Audio-Info: {$info}";
    }

    return $result;
  }

  protected function getAdditionalHeaders(array $config): array {
    if (isset($config['headers']) && is_array($config['headers'])) {
      return array_values($config['headers']);
    }
    return [];
  }

  protected function getHeaders(): array {
    $result = [];

    $config = $this -> config['station'];

    $result[] = 'HTTP/1.0 200 OK';
    $result = array_merge($result, $this -> getStationInfoHeaders($config));
    $result = array_merge($result, $this -> getAudioInfoHeaders($config));
    $result = array_merge($result, $this -> getAdditionalHeaders($config));

    return $result;
  }

  protected function validateConfig(array $config) {
    $config['station'] = @$config['station'];
    if (!is_array($config['station'])) $config['station'] = [];
    $config['station'] = array_merge([
      'format' => 'mp3',
      'metadata' => false,
    ], $config['station']);

    if (!isset($config['type'])) {
      $config['type'] = 'player';
    }

    $clientSupportsMetadata = (int)(@$_SERVER['HTTP_ICY_METADATA']) != 0;
    $serverSupportsMetadata = (bool)$config['station']['metadata'];
    $metadataSupported = $clientSupportsMetadata && $serverSupportsMetadata;

    $config['station']['metadata'] = (bool)$metadataSupported;
    if ($metadataSupported) {
      $metaint = (int)(@$config['station']['metaint']);
      if ($metaint <= 0) $metaint = 65536;
      $config['station']['metaint'] = $metaint;
    }

    return $config;
  }

  public function __construct(array $config = []) {
    $this -> config = $this -> validateConfig($config);
  }

  public function run() {
    set_time_limit(0);

    $graph = @static::$graphs[$this -> config['graph']['type']];
    if (!$graph) return;

    /**
     * @var Graph $graph
     */
    $graph = new $graph();
    $graph -> run($this -> config, $this -> getHeaders());
  }

}
