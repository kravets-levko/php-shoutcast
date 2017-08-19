<?php

namespace Shoutcast;

use Shoutcast\Filter\MetadataSource;
use Shoutcast\Filter\MetadataStream;
use Shoutcast\Filter\Playlist\Blank as BlankPlaylist;
use Shoutcast\Filter\Playlist\Plain as PlainPlaylist;
use Shoutcast\Filter\Playlist\Shuffle as ShufflePlaylist;
use Shoutcast\Filter\Playlist\Loop as LoopPlaylist;
use Shoutcast\Filter\Playlist\Random as RandomPlaylist;

use Shoutcast\Filter\Backend\AVConv as AVConvBackend;
use Shoutcast\Filter\Backend\External as ExternalBackend;
use Shoutcast\Filter\Backend\FFMpeg as FFMpegBackend;
use Shoutcast\Filter\Backend\Raw as RawBackend;

use Shoutcast\Filter\Sink\Client as ClientSink;
use Shoutcast\Filter\Sink\Discard as DiscardSink;
use Shoutcast\Filter\Sink\File as FileSink;

use Shoutcast\Filter\Metadata\Defaults as DefaultsMetadata;
use Shoutcast\Filter\Metadata\FileName as FileNameMetadata;

use Shoutcast\Interfaces\Connector\IInputMediaSource;
use Shoutcast\Interfaces\Connector\IOutputMediaSource;
use Shoutcast\Interfaces\Connector\IOutputMetadataSource;
use Shoutcast\Interfaces\Connector\IOutputMetadataStream;
use Shoutcast\Interfaces\IDataSink;

class Graph {

  protected static $playlist = [
    'blank' => BlankPlaylist::class,
    'plain' => PlainPlaylist::class,
    'loop' => LoopPlaylist::class,
    'shuffle' => ShufflePlaylist::class,
    'random' => RandomPlaylist::class,
  ];

  protected static $backend = [
    'avconv' => AVConvBackend::class,
    'ffmpeg' => FFMpegBackend::class,
    'external' => ExternalBackend::class,
    'raw' => RawBackend::class,
  ];

  protected static $sink = [
    'discard' => DiscardSink::class,
    'file' => FileSink::class,
    'client' => ClientSink::class,
  ];

  protected static $metadata = [
    'defaults' => DefaultsMetadata::class,
    'filename' => FileNameMetadata::class,
  ];

  protected function getFilterConfig(array $config, string $type, string $filter): array {
    $stationConfig = @$config['station'];
    if (!is_array($stationConfig)) $stationConfig = [];

    $filterConfig = @$config['filters'][$type][$filter];
    if (!is_array($filterConfig)) $filterConfig = [];

    return array_merge($stationConfig, $filterConfig);
  }

  protected function connectPlaylist(array $filters, IOutputMediaSource $playlist = null) {
    if ($playlist) {
      /**
       * @var IInputMediaSource $filter
       */
      foreach ($filters as $filter) {
        $filter -> setInputMediaSource($playlist -> getOutputMediaSource());
      }
    }
  }

  protected function createPlaylist(string $filter, array $config = []): ?Filter {
    return $this -> createFilter('playlist', $filter, $config);
  }

  protected function createPlaylistChain(array $filters, array $config = []) {
    // Create filters from beginning of the chain until chain is empty
    // or reached filter that cannot be chained
    $result = [];
    foreach ($filters as $filter) {
      if (is_string($filter)) {
        $filter = $this -> createPlaylist($filter, $config);
      }
      if (is_object($filter) && ($filter instanceof IOutputMediaSource)) {
        $result[] = $filter;
        if (!($filter instanceof IInputMediaSource)) {
          break;
        }
      }
    }

    if (count($result) > 1) {
      // Connect filters starting from the end of the chain
      $result = array_reverse($result);
      $prev = array_shift($result);
      foreach ($result as $filter) {
        /**
         * @var IInputMediaSource $filter
         * @var IOutputMediaSource $prev
         */
        $filter -> setInputMediaSource($prev -> getOutputMediaSource());
        $prev = $filter;
      }
      return end($result);
    } else {
      return reset($result);
    }
  }

  protected function createFilter(string $type, string $filter, array $config = []): ?Filter {
    $filters = [
      'playlist' => static::$playlist,
      'backend' => static::$backend,
      'sink' => static::$sink,
      'metadata' => static::$metadata,
    ];
    if (array_key_exists($type, $filters)) {
      if (array_key_exists($filter, $filters[$type])) {
        $class = $filters[$type][$filter];
        $config = $this -> getFilterConfig($config, $type, $filter);
        return new $class($config);
      }
    }
    return null;
  }

  protected function createBackend(string $filter, array $config = []): ?Filter {
    return $this -> createFilter('backend', $filter, $config);
  }

  protected function createSink(string $filter, array $config = []): ?Filter {
    return $this -> createFilter('sink', $filter, $config);
  }

  protected function createMetadata(string $filter, array $config = []): ?Filter {
    return $this -> createFilter('metadata', $filter, $config);
  }

  protected function createMetadataChain(array $filters, array $config = []): ?Filter {
    $result = new MetadataStream();

    // Data from first item has priority
    $filters = array_reverse($filters);

    foreach ($filters as $filter) {
      if (is_string($filter)) {
        $filter = $this -> createPlaylist($filter, $config);
      }
      if (is_object($filter)) {
        if ($filter instanceof IOutputMetadataSource) {
          $result -> addInputMetadataSource($filter -> getOutputMetadataSource());
        } elseif ($filter instanceof IOutputMetadataStream) {
          $filter2 = new MetadataSource();
          $filter2 -> setInputMetadataStream($filter -> getOutputMetadataStream());
          $result -> addInputMetadataSource($filter2 -> getOutputMetadataSource());
        }
      }
    }

    return $result;
  }

  public function build(array $config = []): ?IDataSink {
    return null;
  }

  public function run(array $config = [], array $headers = []) {
    $sink = $this -> build($config);
    if ($sink) {
      $sink -> drownHeaders($headers);
      if (gc_enabled()) gc_collect_cycles(); // Force freeing memory
      $time = microtime(true);
      while(!connection_aborted()) {
        if (!$sink -> drownData(64 * 1024)) break;
        if (microtime(true) - $time > 2 * 60 * 1000) { // Every 2 minutes
          if (gc_enabled()) gc_collect_cycles(); // Force freeing memory
          $time = microtime(true);
        }
      }
    }
  }

}
