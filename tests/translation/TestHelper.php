<?php

namespace TranslationTest;

use CurlHandle;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final class TestHelper
{
    /**
     * Get contents of a file via URL (http)
     *
     * @param   string  $url  URL of a file.
     *
     * @return   string
     */
    public function getURLContents(string $url): string
    {
        if (function_exists('curl_init')) {

            // Prepare CURL connection
            $connection = $this->createConnection($url);

            // Return response
            $buffer = curl_exec($connection);

        } else {
            $options = [
                'ssl'  => [
                    "verify_peer"      => false,
                    "verify_peer_name" => false
                ],
                'http' => [
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Joomla! Translation Verification',
                ]
            ];

            $buffer = file_get_contents(
                $url, false,
                stream_context_create($options)
            );
        }

        return $buffer;
    }

    /**
     * Prepare CURL connection.
     *
     * @param   string    $url         URL to be used in connection.
     * @param   resource  $fileHandle  File handle to be used in connection.
     *
     * @return CurlHandle
     */
    protected function createConnection(string $url, $fileHandle = null): CurlHandle
    {
        // Initialise connection
        $connection = curl_init();

        // Configure CURL
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] ?? 'Joomla! Translation Verification');

        // Set URL
        curl_setopt($connection, CURLOPT_URL, $url);

        // Set File Handle
        if (!is_null($fileHandle)) {
            curl_setopt($connection, CURLOPT_TIMEOUT, 100);
            curl_setopt($connection, CURLOPT_FILE, $fileHandle);
            curl_setopt($connection, CURLOPT_FOLLOWLOCATION, true);
        }

        return $connection;
    }

    /**
     * Download a file to local filesystem.
     *
     * @param   string  $url
     * @param   string  $path
     */
    public function downloadFile(string $url, string $path): void
    {

        if (function_exists('curl_init')) {
            // Create file handle
            $handle = fopen($path, 'wb+');

            // Prepare CURL connection
            $connection = $this->createConnection($url, $handle);

            // Run CURL
            curl_exec($connection);
            $error = curl_error($connection);
            if (!empty($error)) {
                throw new RuntimeException('(Curl) ' . $error, 502);
            }

            // Close file handle
            fclose($handle);

            // Close CURL connection
            curl_close($connection);

        } else {

            $options = [
                'ssl'  => [
                    "verify_peer"      => false,
                    "verify_peer_name" => false
                ],
                'http' => [
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Joomla! Translation Verification'
                ]
            ];

            file_put_contents(
                $path,
                file_get_contents(
                    $url, false,
                    stream_context_create($options)
                )
            );
        }

        if (file_exists($path)) {
            throw new RuntimeException("Unable to download $url to $path!", 502);
        }
    }

    public function removeDirectory(string $path): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $todo = ($file->isDir() ? 'rmdir' : 'unlink');
            $todo($file->getRealPath());
        }

        rmdir($path);
    }
}