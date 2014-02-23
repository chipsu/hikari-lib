<?php

namespace hikari\application;

/**
 * @todo Cache
 */
class Translator {
    protected $locale;
    protected $strings;
    protected $cache;
    
    public function __construct($locale = null) {
        $this->locale = $locale;
        $this->strings = array();
    }
    
    public function getCache() {
        return $this->cache;
    }
    
    public function setCache($value) {
        $this->cache = $value;
    }
    
    public function initComponent(array $config) {
        if(isset($config['locale'])) $this->locale = $config['locale'];
    }
    
    public function getLocale() {
        return empty($this->locale) ? \Locale::getDefault() : $this->locale;
    }
    
    public function getLocales() {
        return array($this->getLocale(), null);
    }
    
    public function load($fileName) {
        require($fileName);
    }
    
    public function loadPath($path) {
        foreach($this->getLocales() as $locale) {
            $file = $path.'/strings'.($locale ? '.'.$locale : '').'.php';
            if(is_file($file)) {
                $this->load($file);
            }
        }
    }
    
    public function translate($key, array $args = array()) {
        foreach($this->getLocales() as $locale) {
            if(isset($this->strings[$locale][$key])) {
                $format = $this->strings[$locale][$key];
                break;
            }
        }
        if(!isset($format)) {
            if(SU_LOG) \suLog::w('String "%s" does not have a translation for "%s"', $key, $this->getLocale());
            $format = $key;
        }
        return $args ? vsprintf($format, $args) : $format;
    }
    
    public function add($locale, array $strings) {
        $this->strings[$locale] = isset($this->strings[$locale]) ? array_merge($strings, $this->strings[$locale]) : $strings;
    }
}
