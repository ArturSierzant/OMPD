<?php 
namespace AdinanCenci\RadioBrowser;

/**
 * This is simply a wrapper for the RadioBrowserApi class, returning the data 
 * decoded in associative arrays ( or stdClass objects ) for the sake of conveniency.
 */

class RadioBrowser 
{
    /** @var RadioBrowserApi $api */
    protected $api = null;

    /** @var bool $associative Defines wether the methods return associative arrays or stdObjects */
    protected $associative = true;

    public function __construct($server = 'https://de1.api.radio-browser.info/', $associative = true) 
    {
        $this->api = new RadioBrowserApi($server, 'json');
        $this->associative = $associative;
    }

    public function __get($var) 
    {
        return $this->api->__get($var);
    }

    public function __call($name, $arguments) 
    {
        if (! method_exists($this->api, $name)) {
            return null;
        }

        // the only exception
        if ($name == 'getServerMetrics') {
            return $this->api->getServerMetrics();
        }

        $data = call_user_func_array([$this->api, $name], $arguments);

        return json_decode($data, $this->associative);
    }

    public static function __callStatic($name, $arguments) 
    {
        if (! method_exists('\AdinanCenci\RadioBrowser\RadioBrowserApi', $name)) {
            return null;
        }

        return call_user_func_array(['\AdinanCenci\RadioBrowser\RadioBrowserApi', $name], $arguments);
    }
}
