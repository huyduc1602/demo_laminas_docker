<?php
namespace GuzzleHttp\Psr7;
/**
 * Create a new stream based on the input type.
 *
 * Options is an associative array that can contain the following keys:
 * - metadata: Array of custom metadata.
 * - size: Size of the stream.
 *
 * This method accepts the following `$resource` types:
 * - `Psr\Http\Message\StreamInterface`: Returns the value as-is.
 * - `string`: Creates a stream object that uses the given string as the contents.
 * - `resource`: Creates a stream object that wraps the given PHP stream resource.
 * - `Iterator`: If the provided value implements `Iterator`, then a read-only
 *   stream object will be created that wraps the given iterable. Each time the
 *   stream is read from, data from the iterator will fill a buffer and will be
 *   continuously called until the buffer is equal to the requested read size.
 *   Subsequent read calls will first read from the buffer and then call `next`
 *   on the underlying iterator until it is exhausted.
 * - `object` with `__toString()`: If the object has the `__toString()` method,
 *   the object will be cast to a string and then a stream will be returned that
 *   uses the string value.
 * - `NULL`: When `null` is passed, an empty stream object is returned.
 * - `callable` When a callable is passed, a read-only stream object will be
 *   created that invokes the given callable. The callable is invoked with the
 *   number of suggested bytes to read. The callable can return any number of
 *   bytes, but MUST return `false` when there is no more data to return. The
 *   stream object that wraps the callable will invoke the callable until the
 *   number of requested bytes are available. Any additional bytes will be
 *   buffered and used in subsequent reads.
 *
 * @param resource|string|int|float|bool|StreamInterface|callable|\Iterator|null $resource Entity body data
 * @param array                                                                  $options  Additional options
 *
 * @return StreamInterface
 *
 * @throws \InvalidArgumentException if the $resource arg is not valid.
 *
 * @deprecated stream_for will be removed in guzzlehttp/psr7:2.0. Use Utils::streamFor instead.
 */
function stream_for($resource = '', array $options = [])
{
    return \GuzzleHttp\Psr7\Utils::streamFor($resource, $options);
}