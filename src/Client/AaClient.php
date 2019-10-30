<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\Client;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class AaClient
{
    protected $curl;
    protected $url = 'https://hrbaxml.arbeitsagentur.de';
    protected $certPath;
    protected $cachePath;
    protected $name;

    public function __construct(string $certPath, string $cachePath)
    {
        $this->certPath = rtrim($certPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->cachePath = rtrim($cachePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
        $this->curl = null;

        return $this;
    }

    public function open(string $name)
    {
        $this->name = $name;
        if (is_resource($this->curl)) {
            curl_reset($this->curl);
        }

        $sslCert = $this->certPath . $name . '.crt.pem';
        $sslKey = $this->certPath . $name . '.key.pem';
        $sslCa = $this->certPath . $name . '.ca.pem';

        $ch = curl_init();

        $options = [
            CURLOPT_SSLCERT => $sslCert,
            CURLOPT_SSLKEY => $sslKey,
            CURLOPT_CAINFO => $sslCa,
            CURLOPT_RETURNTRANSFER => true
        ];

        curl_setopt_array($ch, $options);

        $this->curl = $ch;

        return $this;
    }

    public function setUrl($path)
    {
        $this->open();
        $url = $this->url . '/' . ltrim($path, '/');

        curl_setopt($this->curl, CURLOPT_URL, $url);

        return $this;
    }

    public function get($path = '')
    {
        $this->setUrl($path);
        return $this->query();
    }

    public function download($path, $overwrite=false)
    {
        $this->setUrl($path);

        $target = $this->cachePath . 'download/' . $this->name . '/' . ltrim($path, '/');

        if (file_exists($target) && !$overwrite) {
            return false;
        }

        $fp = fopen ($target, 'w');

        curl_setopt($this->curl, CURLOPT_FILE, $fp);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 50);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);

        $result = $this->query();

        fclose($fp);

        $result['file'] = $target;

        return $result;
    }

    public function upload($file)
    {
        $this->setUrl('in/upload.php');
        $curlFile = new \CURLFile($file);
        $data     = [ 'upload' => $curlFile ];

        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);

        $result = $this->query();
        $err = curl_error($this->curl);
        return $result;

    }

    public function delete($file){
        $this->setUrl($file);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        $result = curl_exec($this->curl);
        return $result;
    }


    protected function query()
    {
        $result = curl_exec($this->curl);
        $code   = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        $clean  = preg_replace('~^.*<body>(.*)</body>.*$~is', '$1', $result);
        $error = 200 > $code || 300 <= $code;
        return [ 'code' => $code, 'result' => $result, 'message' => $clean, 'error' => $error ];
    }
}
