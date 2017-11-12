<?php

namespace src;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class DownloadHelper
{
    /* @var Client */
    private $guzzle;

    /**
     * @var string
     */
    private $serverTemplate = 'https://martin.csgofastbackend.com/socket.io';

    // GUZZLE SETTINGS
    /**
     * @var int
     */
    private $timeout = 3;
    /**
     * @var bool
     */
    private $debug = false;
    /**
     * @var bool
     */
    private $cookies = true;

    // REQUEST PARAMS
    /**
     * @var string
     */
    private $sid;
    /**
     * @var string
     */
    private $timeParam;

    //PARAMS

    /**
     * @var int
     */
    private $trying = 3;

    /**
     * @var int
     */
    private $sleep = 1;

    /**
     * HistoryDownloader constructor.
     */
    public function __construct()
    {
        $this->guzzle = new Client([
            'cookies' => $this->cookies,
            'debug' => $this->debug,
            'timeout' => $this->timeout,
        ]);
    }

    /**
     * Return history
     * @return array | null
     */
    public function load()
    {
        $counter = 0;
        while (true) {
            $counter++;
            if ($counter > $this->trying) {
                echo "Can't load History ({$this->trying} trying)" . PHP_EOL;
                break;
            }
            $this->requestSid();
            if ($this->sid == null) {
                echo "Can't receive SID" . PHP_EOL;
                break;
            }
            $response = $this->requestInit();
            if ($response == null) {
                echo "Can't init server" . PHP_EOL;
                break;
            }
            $result = $this->requestHistoryInit();
            if ($result == false) {
                echo "Can't init history" . PHP_EOL;
                break;
            }
            $result = $this->requestHistory();
            if ($result !== null) {
                return $result;
            }
            sleep ($this->sleep);
        }

        return null;
    }

    private function requestSid()
    {
        $this->generateTimeParam();
        $server = $this->getServerURL();
        $url = $server . "/?EIO=3&transport=polling&t=$this->timeParam";
        $response = $this->request('GET', $url);
        
        if ($response != null) {
            $json = json_decode(substr(trim($response), 3));
            $this->sid = $json->sid;
        }
    }

    private function requestInit()
    {
        $this->generateTimeParam();
        $server = $this->getServerURL();
        $url = $server . "/?EIO=3&transport=polling&t=$this->timeParam&sid=$this->sid";
        $response = $this->request('GET', $url);

        return $response;
    }

    private function requestHistoryInit()
    {
        $this->generateTimeParam();
        $server = $this->getServerURL();
        $url = $server . "/?EIO=3&transport=polling&t=$this->timeParam&sid=$this->sid";
        $body = ['body' => '38:42["history-request",{"gameTypeId":3}]'];
        $response = $this->request('POST', $url, $body);
        if ($response == 'ok') {
            return true;
        }
        return false;
    }

    private function requestHistory()
    {
        $this->generateTimeParam();
        $server = $this->getServerURL();
        $url = $server . "/?EIO=3&transport=polling&t=$this->timeParam&sid=$this->sid";
        $response = $this->request('GET', $url);
        $raw = trim($response);
        $raw = substr($raw, stripos($raw, "["));
        $history = json_decode($raw);

        return $history[1]->history;
    }

    private function request($method, $url, $body = null)
    {
        try {
            $request = new Request($method, $url, self::getHeaders());
            if ($body !== null) {
                $response = $this->guzzle->send($request, $body);
            } else {
                $response = $this->guzzle->send($request);
            }

            if ($response->getStatusCode() == 200) {
                return $response->getBody()->getContents();
            } else {
                return null;
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage() . PHP_EOL;
            return null;
        }
    }

    private function getHeaders()
    {
        return [
            'Accept' => '*/*',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'ru,en-US;q=0.8,en;q=0.6,uk;q=0.4',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'DNT' => 1,
            'Pragma' => 'no-cache',
            'Upgrade-Insecure-Requests' => 1,
            'X-Compress' => 'null',
            'Referer' => 'http://csgofast.com/',
            'Origin' => 'http://csgofast.com'
        ];
    }

    private function generateTimeParam()
    {
        $result = "";
        $dict = str_split('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_');
        $time = round(microtime(true) * 1000);
        do {
            $result = $dict[$time % count($dict)] . $result;
            $time = floor($time / count($dict));
        } while ($time > 0);

        return $this->timeParam = $result;
    }

    private function getServerURL()
    {
        return $this->serverTemplate;
    }
}
