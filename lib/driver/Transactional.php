<?php

declare(strict_types=1);

/*
 * (c) Andrey F. Mindubaev <covex.mobile@gmail.com>

           - (c) edited by frdlweb
 */

use Covex\Stream;

use Covex\Stream\File\Entity;
use Covex\Stream\File\EntityInterface;

//Covex\Stream\Filesystem
class Transactional
{
    /**
     * @var Partition[]
     */
    protected static $partitions = [];

    /**
     * @var array
     */
    protected $dirFiles;

    /**
     * @var resource
     */
    protected $filePointer;

    /**
     * @var EntityInterface
     */
    protected $fileEntity;

    public function __construct()
    {
        $this->filePointer = null;
        $this->fileEntity = null;
    }

    /**
     * Retrieve information about a file.
     *
     * @return array|bool
     */
    public function url_stat(string $url, int $flags)
    {
        $partition = static::getPartition($url);
        $path = static::getRelativePath($url);

        return $partition->getStat($path, $flags);
    }

    /**
     * Create a directory. This method is called in response to mkdir().
     *
     * @see http://www.php.net/manual/en/streamwrapper.mkdir.php
     */
    public function mkdir(string $url, int $mode, int $options): bool
    {
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        return (bool) $partition->makeDirectory($path, $mode, $options);
    }

    /**
     * Removes a directory. This method is called in response to rmdir().
     *
     * @see http://www.php.net/manual/en/streamwrapper.rmdir.php
     */
    public function rmdir(string $url, int $options): bool
    {
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        return (bool) $partition->removeDirectory($path, $options);
    }

    /**
     * Delete a file. This method is called in response to unlink().
     *
     * @see http://www.php.net/manual/en/streamwrapper.unlink.php
     */
    public function unlink(string $url): bool
    {
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        return (bool) $partition->deleteFile($path);
    }

    /**
     * Rename a file or directory.
     *
     * @see http://www.php.net/manual/en/streamwrapper.rename.php
     */
    public function rename(string $srcPath, string $dstPath): bool
    {
        $partition = self::getPartition($srcPath);

        $srcRelativePath = self::getRelativePath($srcPath);
        $dstRelativePath = self::getRelativePath($dstPath);

        return (bool) $partition->rename($srcRelativePath, $dstRelativePath);
    }

    /**
     * Open directory handle. This method is called in response to opendir().
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-opendir.php
     */
    public function dir_opendir(string $url): bool
    {
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        $files = $partition->getList($path);
        if (is_array($files)) {
            $this->dirFiles = [];
            foreach ($files as $file) {
                $this->dirFiles[] = $file->basename();
            }
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Read entry from directory handle. This method is called in response to readdir().
     *
     * @return string|bool
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-readdir.php
     */
    public function dir_readdir()
    {
        $value = current($this->dirFiles);

        if (false === $value) {
            $result = false;
        } else {
            $result = $value;
            next($this->dirFiles);
        }

        return $result;
    }

    /**
     * Close directory handle. This method is called in response to closedir().
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-closedir.php
     */
    public function dir_closedir(): bool
    {
        unset($this->dirFiles);

        return true;
    }

    /**
     * Rewind directory handle. This method is called in response to rewinddir().
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-rewinddir.php
     */
    public function dir_rewinddir(): bool
    {
        reset($this->dirFiles);

        return true;
    }

    /**
     * Opens file or URL. This method is called immediately after the wrapper is initialized.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-open.php
     */
    public function stream_open(string $url, string $mode, int $options, ?string &$openedPath): bool
    {
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        $this->filePointer = $partition->fileOpen(
            $path, $mode, $options
        );

        $result = (bool) $this->filePointer;
        if ($result && ($options & STREAM_USE_PATH)) {
            $openedPath = $path;
        }

        return $result;
    }

    /**
     * Close an resource. This method is called in response to fclose().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-close.php
     */
    public function stream_close(): void
    {
        fclose($this->filePointer);
    }

    /**
     * Read from stream. This method is called in response to fread() and fgets().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-read.php
     */
    public function stream_read(int $count): string
    {
        return fread($this->filePointer, $count);
    }

    /**
     * Retrieve information about a file resource. This method is called in response to fstat().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-stat.php
     */
    public function stream_stat(): array
    {
        return fstat($this->filePointer);
    }

    /**
     * Tests for end-of-file on a file pointer. This method is called in response to feof().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-eof.php
     */
    public function stream_eof(): bool
    {
        return feof($this->filePointer);
    }

    /**
     * Retrieve the current position of a stream. This method is called in response to ftell().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-tell.php
     */
    public function stream_tell(): int
    {
        return ftell($this->filePointer);
    }

    /**
     * Seeks to specific location in a stream. This method is called in response to fseek().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-seek.php
     */
    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        return 0 === fseek($this->filePointer, $offset, $whence);
    }

    /**
     * Write to stream. This method is called in response to fwrite().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-write.php
     */
    public function stream_write(string $data): int
    {
        return fwrite($this->filePointer, $data);
    }

    /**
     * Flushes the output. This method is called in response to fflush().
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-flush.php
     */
    public function stream_flush(): bool
    {
        return fflush($this->filePointer);
    }

    /**
     * Change stream metadata.
     *
     * @param mixed $value
     *
     * @see http://php.net/manual/ru/streamwrapper.stream-metadata.php
     */
    public function stream_metadata(string $url, int $option, $value): bool
    {
        $partition = self::getPartition($url);
        $path = self::getRelativePath($url);

        switch ($option) {
            case STREAM_META_TOUCH:
                $result = $partition->touch($path, $value[1] ?? null, $value[2] ?? null) ? true : false;
                break;
            default:
                $result = false;
        }

        return $result;
    }

    /**
     * Register stream wrapper.
     */
    public static function register(string $protocol, string $root = null, int $flags = 0): bool
    {
        $wrappers = stream_get_wrappers();
        if (in_array($protocol, $wrappers)) {
            throw new Exception(
                "Protocol '$protocol' has been already registered"
            );
        }
        $wrapper = stream_wrapper_register($protocol, get_called_class(), $flags);

        if ($wrapper) {
            if (null !== $root) {
                $content = Entity::newInstance($root);
            } else {
                $content = null;
            }

            $partition = new Partition($content);

            self::$partitions[$protocol] = $partition;
        }

        return $wrapper;
    }

    /**
     * Commit all changes to real FS.
     */
    public static function commit(string $protocol): bool
    {
        if (isset(self::$partitions[$protocol])) {
            self::$partitions[$protocol]->commit();

            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Unregister stream wrapper.
     */
    public static function unregister(string $protocol): bool
    {
        unset(self::$partitions[$protocol]);

        $wrappers = stream_get_wrappers();
        if (!in_array($protocol, $wrappers)) {
            throw new Exception(
                "Protocol '$protocol' has not been registered yet"
            );
        }

        return stream_wrapper_unregister($protocol);
    }

    /**
     * Get relative path of an url.
     */
    public static function getRelativePath(string $url): string
    {
        $urlParts = explode('://', $url);
        array_shift($urlParts);
        $urlPath = implode('://', $urlParts);

        return Entity::fixPath($urlPath);
    }

    /**
     * Get partition by file url.
     */
    private static function getPartition(string $url): ?Partition
    {
        $urlParts = explode('://', $url);
        $protocol = array_shift($urlParts);

        return self::$partitions[$protocol] ?? null;
    }
}
