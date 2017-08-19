<?php

namespace Shoutcast\Graph;

use Shoutcast\Filter\Multiplex;
use Shoutcast\Graph;
use Shoutcast\Interfaces\Connector\IInputDataStream;
use Shoutcast\Interfaces\Connector\IInputMediaSource;
use Shoutcast\Interfaces\Connector\IInputMultiplexedStream;
use Shoutcast\Interfaces\Connector\IOutputDataStream;
use Shoutcast\Interfaces\Connector\IOutputMediaSource;
use Shoutcast\Interfaces\Connector\IOutputMetadataStream;
use Shoutcast\Interfaces\IDataSink;

class Player extends Graph {

  public function build(array $config = []): ?IDataSink {
    $graphConfig = $config['graph'];

    if (is_array($graphConfig['playlist'])) {
      $playlist = $this -> createPlaylistChain($graphConfig['playlist'], $config);
    } else {
      $playlist = $this -> createPlaylist($graphConfig['playlist'], $config);
    }

    $backend = $this -> createBackend($graphConfig['backend'], $config);

    /**
     * @var IOutputMediaSource $playlist
     * @var IInputMediaSource $backend
     */
    $backend -> setInputMediaSource($playlist -> getOutputMediaSource());

    $sink = $this -> createSink($graphConfig['sink'], $config);

    if (@$config['station']['metadata']) {
      // If both server and client supports metadata:
      // 1. link all metadata sources with MetadataStream filter
      // 2. link MetadataFilter and backend to Multiplex filter
      // 3. link Multiplex filter to sink

      $metadata = $graphConfig['metadata'];
      if (!is_array($metadata)) $metadata = [$metadata];
      $metadata = array_filter(array_values($metadata), 'is_string');
      $metadata = array_map(function($filter) use ($backend) {
        switch ($filter) {
          case 'backend': return $backend;
          default: return $filter;
        }
      }, $metadata);
      $metadata = $this -> createMetadataChain($metadata, $config);

      $mux = new Multiplex(array_intersect_key(
        $config['station'],
        array_flip(['metaint'])
      ));
      /**
       * @var IOutputDataStream $backend
       */
      $mux -> setInputDataStream($backend -> getOutputDataStream());
      /**
       * @var IOutputMetadataStream $metadata
       */
      $mux -> setInputMetadataStream($metadata -> getOutputMetadataStream());

      /**
       * @var IInputMultiplexedStream $sink
       */
      $sink -> setInputMultiplexedStream($mux -> getOutputMultiplexedStream());
    } else {
      // If metadata not supported by client and/or server - link backend to sink
      /**
       * @var IOutputDataStream $backend
       * @var IInputDataStream $sink
       */
      $sink -> setInputDataStream($backend -> getOutputDataStream());
    }

    /**
     * @var IDataSink $sink
     */
    return $sink;
  }

}
