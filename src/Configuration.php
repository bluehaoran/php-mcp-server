<?php

declare(strict_types=1);

namespace PhpMcp\Server;

use PhpMcp\Server\Model\Capabilities;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use React\EventLoop\LoopInterface;

/**
 * Value Object holding core configuration and shared dependencies for the MCP Server instance.
 *
 * This object is typically assembled by the ServerBuilder and passed to the Server constructor.
 */
class Configuration
{
    /**
     * @param  string  $serverName  Name of this MCP server application.
     * @param  string  $serverVersion  Version of this MCP server application.
     * @param  Capabilities  $capabilities  Capabilities of this MCP server application.
     * @param  LoggerInterface  $logger  PSR-3 Logger instance.
     * @param  LoopInterface  $loop  ReactPHP Event Loop instance.
     * @param  CacheInterface|null  $cache  Optional PSR-16 Cache instance for registry/state.
     * @param  ContainerInterface  $container  PSR-11 DI Container for resolving handlers/dependencies.
     * @param  int  $definitionCacheTtl  TTL in seconds for cached definitions (if cache is provided).
     * @param  int  $paginationLimit  Maximum number of items to return for list methods.
     */
    public function __construct(
        public readonly string $serverName,
        public readonly string $serverVersion,
        public readonly Capabilities $capabilities,
        public readonly LoggerInterface $logger,
        public readonly LoopInterface $loop,
        public readonly ?CacheInterface $cache,
        public readonly ContainerInterface $container,
        public readonly int $definitionCacheTtl = 3600,
        public readonly int $paginationLimit = 50,
    ) {}
}
