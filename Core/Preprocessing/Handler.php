<?php
class Handler
{
    // This function feels too long
    public static function Handle($raw_request)
    {
        /*
         * Split up the request components
         */
        // Split the query string from the URL
        $split_request = explode('?', $raw_request);

        // Use regex to split the main request up (the first match for explode)
        $uri_pattern = '~^/?(\w*)(/?((\w*\/?)*))?$~';
        $matches = array();
        preg_match($uri_pattern, $split_request[0], $matches);

        // Split the data bits up (as in /controller/action/data1/data2/data3)
        $raw_request_data = $matches[3];
        $nice_data = explode('/', $raw_request_data);

        // Split the query string up
        if (isset($split_request[1]))
        {
            $nice_query_dirty = explode('&', $split_request[1]);
            $nice_query = array();

            foreach ($nice_query_dirty as $query)
            {
                $bits = explode('=', $query);

                if (isset($bits[1]))
                {
                    $nice_query[$bits[0]] = $bits[1];
                }
                else
                {
                    $nice_query[$bits[0]] = true;
                }
            }
        }
        else
        {
            $nice_query = array();
        }

        /*
         * Create controller and send it on its way (HomeController if not specified)
         * No error checking yet!
         */
        $controllerType = ucfirst(strtolower($matches[1]));

        if (strlen($controllerType) === 0)
        {
            $controllerType = 'Home';
        }

        $controllerName =  $controllerType . 'Controller';
        require INDEX_DIR . 'Application/Controller/' . $controllerName . '.php';

        try
        {
            $controller = new $controllerName($nice_data, $nice_query);
            $controller->Go(strtolower($_SERVER['REQUEST_METHOD']));
        }
        catch (Exception $exception)
        {
            $handler = new ErrorHandler($exception);
            $handler->Display();
        }
    }
}
