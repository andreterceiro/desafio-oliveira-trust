<?php
require_once("libs/ViewManager.php");
class Config {
    const CONVERSION_API_HTTP_ADDRESS = 'https://economia.awesomeapi.com.br/json/last';
    const LIST_CURRENCIES_HTTP_ADDRESS = 'https://economia.awesomeapi.com.br/xml/available/uniq';

    public static function getStartingConversionApiHttpAddress() {
        return self::CONVERSION_API_HTTP_ADDRESS . "/";
    }
}