<?php
/**
 * Class that manage general things to deal with views
 */
class ViewManager {
    /**
     * Render a view
     * 
     * @static
     * @throws \ValueError if a view with the name received (through a parameter) could not be found
     *
     * @param string $view Name of the view without the file extension
     * 
     * @return void
     */
    public static function render(string $view): void {
        $file = __DIR__ . "/../templates/${view}.php";

        if (! is_file($file)) {
            throw new \ValueError("There is not a view with this name");
        }

        require_once $file;
    }
}