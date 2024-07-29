<?php
require_once("libs/ViewManager.php");

/**
 * Class to store general things related to configurations
 */
class Config {
    const CONVERSION_API_HTTP_ADDRESS = 'https://economia.awesomeapi.com.br/json/last';
    const LIST_CURRENCIES_HTTP_ADDRESS = 'https://economia.awesomeapi.com.br/xml/available/uniq';

    /**
     * Returns the configured the starting og the address of the conversion API URL
     * You will need to concat the acronyms of the currencies to convert, like USD-BRL
     * ATENTION to the example: with a dash between the curencies
     *
     * @return string
     */
    public static function getStartingConversionApiHttpAddress() {
        return self::CONVERSION_API_HTTP_ADDRESS . "/";
    }
}