<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */
namespace trntv\filekit\base;

use yii\base\Object;

class Url extends Object{

    private $_scheme;
    private $_host;
    private $_port;
    private $_user;
    private $_pass;
    private $_path;
    private $_query;
    private $_fragment;

    private $_originalUrl;

    /**
     * @param $url
     * @return mixed
     */
    public function setUrl($url)
    {
        $this->_originalUrl = $url;
        $_url = parse_url($url);
        if($_url){
            foreach($_url as $k => $v){
                $this->{$k} = $v;
            }
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return \http_build_url([
            'scheme'=>$this->_scheme,
            'host'=>$this->_host,
            'port'=>$this->_port,
            'user'=>$this->_user,
            'pass'=>$this->_pass,
            'path'=>$this->_path,
            'query'=>$this->_query,
            'fragment'=>$this->_fragment
        ]);
    }

    /**
     * @return mixed
     */
    public function getFragment()
    {
        return $this->_fragment;
    }

    /**
     * @param mixed $fragment
     * @return mixed
     */
    public function setFragment($fragment)
    {
        return $this->_fragment = $fragment;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * @param mixed $host
     * @return mixed
     */
    public function setHost($host)
    {
        return $this->_host = $host;
    }

    /**
     * @return mixed
     */
    public function getPass()
    {
        return $this->_pass;
    }

    /**
     * @param mixed $pass
     * @return mixed
     */
    public function setPass($pass)
    {
        return $this->_pass = $pass;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @param mixed $path
     * @return mixed
     */
    public function setPath($path)
    {
        return $this->_path;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     * @param mixed $port
     * @return mixed
     */
    public function setPort($port)
    {
        return $this->_port = $port;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * @param mixed $query
     * @return mixed
     */
    public function setQuery($query)
    {
        return $this->_query = $query;
    }

    /**
     * @return mixed
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * @param mixed $scheme
     * @return mixed
     */
    public function setScheme($scheme)
    {
        return $this->_scheme = $scheme;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param mixed $user
     * @return mixed
     */
    public function setUser($user)
    {
        return $this->_user = $user;
    }

    /**
     * @return mixed
     */
    public function getOriginalUrl()
    {
        return $this->_originalUrl;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUrl();
    }
}