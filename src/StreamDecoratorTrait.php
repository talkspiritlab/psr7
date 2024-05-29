<?php

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator trait
 *
 * @property StreamInterface stream
 */
trait StreamDecoratorTrait
{
    private ?StreamInterface $stream = null;

    /**
     * @param StreamInterface $stream Stream to decorate
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }


    public function lazyStream(): StreamInterface
    {
        return $this->stream ??= $this->createStream();
    }

    public function __toString()
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }
            return $this->getContents();
        } catch (\Exception $e) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            trigger_error('StreamDecorator::__toString exception: '
                . (string) $e, E_USER_ERROR);
            return '';
        }
    }

    public function getContents()
    {
        return Utils::copyToString($this);
    }

    /**
     * Allow decorators to implement custom methods
     *
     * @param string $method Missing method name
     * @param array  $args   Method arguments
     *
     * @return mixed
     */
    public function __call($method, array $args)
    {
        $result = call_user_func_array([$this->lazyStream(), $method], $args);

        // Always return the wrapped object if the result is a return $this
        return $result === $this->lazyStream() ? $this : $result;
    }

    public function close()
    {
        $this->lazyStream()->close();
    }

    public function getMetadata($key = null)
    {
        return $this->lazyStream()->getMetadata($key);
    }

    public function detach()
    {
        return $this->lazyStream()->detach();
    }

    public function getSize()
    {
        return $this->lazyStream()->getSize();
    }

    public function eof()
    {
        return $this->lazyStream()->eof();
    }

    public function tell()
    {
        return $this->lazyStream()->tell();
    }

    public function isReadable()
    {
        return $this->lazyStream()->isReadable();
    }

    public function isWritable()
    {
        return $this->lazyStream()->isWritable();
    }

    public function isSeekable()
    {
        return $this->lazyStream()->isSeekable();
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        $this->lazyStream()->seek($offset, $whence);
    }

    public function read($length)
    {
        return $this->lazyStream()->read($length);
    }

    public function write($string)
    {
        return $this->lazyStream()->write($string);
    }

    /**
     * Implement in subclasses to dynamically create streams when requested.
     *
     * @return StreamInterface
     *
     * @throws \BadMethodCallException
     */
    protected function createStream()
    {
        throw new \BadMethodCallException('Not implemented');
    }
}
