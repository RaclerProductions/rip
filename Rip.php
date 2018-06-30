<?php

namespace Rip {

    /**
     * RIP = REST in PHP
     *
     * Copyright 2018 Racler Produtions
     * @license MIT, see LICENSE for details
     * @author Tim Stachorra <tim.stachorra@racler.productions>
     *
     * @version 0.1.0
     *
     * required PHP version:
     * PHP 4 >= 4.3.0, PHP 5, PHP 7 file_get_contents
     * PHP 5 >= 5.4.0, PHP 7 http_response_code
     * PHP 4 >= 4.0.6, PHP 5, PHP 7 is_callable
     * PHP 4 >= 4.0.7, PHP 5, PHP 7 array_key_exists
     */
    class Rip {
        //Depth starting from the root directory.(This parameter can be skipped if the application is in the root directory.)
        private $dirDepth;
        //Customize the filter response array key (Default = AUTH_RESPONSE)
        private $filterResponseKey;

        /**
         * Rip constructor.
         * @since 0.1.0
         * @param array $prop with the following optional contents:
         * dirDepth and filterResponseKey see below for explanation
         *
         */
        public function __construct($prop) {
            if (isset ($prop ["dirDepth"]) || array_key_exists("dirDepth", $prop)) {
                $this->dirDepth = intval($prop ["dirDepth"]);
            } else {
                $this->dirDepth = 0;
            }
            if (isset($prop["filterResponseKey"]) || array_key_exists("filterResponseKey", $prop)) {
                $this->filterResponseKey = $prop["filterResponseKey"];
            } else {
                $this->filterResponseKey = "AUTH_RESPONSE";
            }
        }


        /**
         * @param Object $response includes the response
         * @param int $statusCode the status code
         * @param string $contentType the content type
         *
         * Set the response header and terminate the application
         * @since 0.1.0
         *
         */
        public static function response($response, $statusCode = 200, $contentType = 'text/plain') {
            http_response_code($statusCode);
            header('Content-Type: ' . $contentType);
            die($response);
        }

        /**
         * Use this to create a GET resource
         * @since 0.1.0
         *
         * @param String $path the resource path
         * @param array $parameters the parameters of the request
         * @param callable $authFilter optional filter which is called before the function is executed
         * @param callable $service the function that is called when the resource is requested
         *
         */
        public function get($path, $parameters, $authFilter, $service) {

            // Check if this resource is requested
            if ($this->isRequested($path, 'GET')) {

                // Calls the filter function
                if (is_callable($authFilter)) {
                    $authResponse = $authFilter ();
                } else {
                    $authResponse = "no response";
                }

                // Get the path parameters
                $var = $this->getGetParameter($path, $parameters);

                $var [$this->filterResponseKey] = $authResponse;

                // Calls the service function with the parameters
                if (is_callable($service)) {
                    echo $service ($var);
                }
            }
        }

        /**
         * Use this to create a POST resource
         * @since 0.1.0
         *
         * @param String $path the resource path
         * @param array $parameters the parameters of the request
         * @param callable $authFilter optional filter which is called before the function is executed
         * @param callable $service the function that is called when the resource is requested
         *
         */
        public function post($path, $parameters, $authFilter, $service) {

            // Check if this resource is requested
            if ($this->isRequested($path, 'POST')) {

                // Calls the filter function
                if (is_callable($authFilter)) {
                    $authResponse = $authFilter ();
                } else {
                    $authResponse = "no response";
                }

                // Get the post parameters
                $var = $this->getPostParameter($parameters);

                $var [$this->filterResponseKey] = $authResponse;

                // Calls the service function with the parameters
                if (is_callable($service)) {
                    echo $service ($var);
                }
            }
        }

        /**
         * Use this to create a PUT resource
         * @since 0.1.0
         *
         * @param String $path the resource path
         * @param array $parameters the parameters of the request
         * @param callable $authFilter optional filter which is called before the function is executed
         * @param callable $service the function that is called when the resource is requested
         *
         */
        public function put($path, $parameters, $authFilter, $service) {
            // Check if this resource is requested
            if ($this->isRequested($path, 'PUT')) {
                // Calls the filter function
                if (is_callable($authFilter)) {
                    $authResponse = $authFilter ();
                } else {
                    $authResponse = "no response";
                }

                // Get the post parameters
                $var = $this->getRequestParameter($parameters);

                $var [$this->filterResponseKey] = $authResponse;

                // Calls the service function with the parameters
                if (is_callable($service)) {
                    echo $service($var);
                }
            }
        }

        /**
         * Use this to create a DELETE resource
         * @since 0.1.0
         *
         * @param String $path the resource path
         * @param array $parameters the parameters of the request
         * @param callable $authFilter optional filter which is called before the function is executed
         * @param callable $service the function that is called when the resource is requested
         *
         */
        public function delete($path, $parameters, $authFilter, $service) {
            // Check if this resource is requested
            if ($this->isRequested($path, 'DELETE')) {
                // Calls the filter function
                if (is_callable($authFilter)) {
                    $authResponse = $authFilter ();
                } else {
                    $authResponse = "no response";
                }

                // Get the post parameters
                $var = $this->getRequestParameter($parameters);

                $var [$this->filterResponseKey] = $authResponse;

                // Calls the service function with the parameters
                if (is_callable($service)) {
                    echo $service($var);
                }
            }
        }

        /**
         * Validate the required request parameters
         * @since 0.1.0
         *
         * @param string $path the requested resource path
         * @param array $parameters the array with the required parameters
         * @return array|string an array with the transmitted parameters.
         *
         */
        private function getGetParameter($path, $parameters) {

            // Checks first if any param is given
            if ($parameters != null) {

                // Get the current path of the request
                $currentPathArray = $this->getCurrentPathArray();

                // Get the path which should be the same as the current path
                $givenPathArray = $this->getGivenPathArray($path);

                // Maps the current path and the given path to check the parameters
                $paramMap = $this->pathGetMapping($currentPathArray, $givenPathArray);

                $var = array();
                // A loop which checks every required param with the map of the founded path parameters
                foreach ($parameters as $key) {
                    // If the required parameter is not in the path
                    if (!isset ($paramMap [$key])) {
                        // stops the service
                        die("Missing GET param");
                    } else {
                        // Set the parameter to the return array
                        $var [$key] = $paramMap [$key];
                    }
                }

                return $var;
            }

            return null;
        }

        /**
         * Maps the path to find out the transmitted parameters
         * @since 0.1.0
         *
         * @param string array $currentPathArray the real requested path
         * @param string array $givenPathArray the required path of the checked resource
         * @return array[]|string
         */
        private function pathGetMapping($currentPathArray, $givenPathArray) {

            // Get the size of the current path array
            $currentPathSize = count($currentPathArray);

            $returnMap = array();
            // for each path piece in the given array start from the last entry
            for ($i = count($givenPathArray); $i > 0; $i--) {

                // if the piece a parameter map it
                if (@substr($givenPathArray [$i], 0, 1) == ':') {
                    // Add the parameter value with the given key to the return map
                    $returnMap [substr($givenPathArray [$i], 1)] = $currentPathArray [$currentPathSize];
                }
                // reduce the current path size
                $currentPathSize--;
            }

            return $returnMap;
        }

        /**
         * Get the parameters which are passed by post
         * @since 0.1.0
         *
         * @param string array $parameter the required parameters in an array
         * @return array[]|string the transmitted parameters mapped in an array
         */
        private function getPostParameter($parameter) {

            // check first if parameters are required
            if ($parameter != null) {
                $var = array();
                // required parameters
                foreach ($parameter as $key) {
                    // check if the parameters has been posted
                    if (!isset ($_POST [$key])) {
                        // Close if the post param was not send
                        die ("Missing POST param");
                    } else {
                        // Add the posted parameters to the map
                        $var [$key] = $_POST [$key];
                    }
                }

                return $var;
            }

            return null;
        }

        /**
         * Get the parameters which are passed by put or delete
         * @since 0.1.0
         *
         * @param string array $parameter the required parameters in an array
         * @return array[]|string the transmitted parameters mapped in an array
         */
        private function getRequestParameter($parameter) {
            parse_str(file_get_contents("php://input"), $_PARAM);

            if ($parameter != null) {

                $var = array();

                foreach ($parameter as $key) {

                    if (!isset($_PARAM[$key])) {

                        die("Missing parameter");
                    } else {

                        $var[$key] = $_PARAM[$key];
                    }
                }

                return $var;
            }
            return null;
        }

        /**
         * Checks if the path matched this resource
         * @since 0.1.0
         *
         * @param string $path the required path
         * @param string $checkMethod the method (POST, GET, PUT or DELETE)
         * @return boolean true if the method and path are matched this resource
         */
        private function isRequested($path, $checkMethod) {

            // Get the request method
            $method = $_SERVER ['REQUEST_METHOD'];

            if ($method == $checkMethod && $this->pathMatches($path)) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Compare the transmitted arrays to check if the path matches each other
         * @since 0.1.0
         *
         * @param string $path the required path of the resource
         * @return boolean true if the path has a match
         */
        private function pathMatches($path) {
            $currentPath = $this->getCurrentPathArray();

            $givenPath = $this->getGivenPathArray($path);

            $currentPathSize = count($currentPath);

            $givenPathSize = count($givenPath);

            $check1 = $currentPathSize - $this->dirDepth;

            if ($check1 != $givenPathSize) {
                return false;
            }

            $acceptRequest = true;

            for ($i = $givenPathSize; $i > 0; $i--) {

                if (@!$this->resourceIsRight($givenPath [$i], $currentPath [$currentPathSize])) {
                    $acceptRequest = false;
                }
                $currentPathSize--;
            }

            return $acceptRequest;
        }

        /**
         * Check if the path piece is right
         * @since 0.1.0
         *
         * @param $res string the real path position
         * @param $checkRes string the required path position
         * @return boolean true if the resource if right
         */
        private function resourceIsRight($res, $checkRes) {
            if (substr($res, 0, 1) == ':') {
                return true;
            }
            if ($res == $checkRes) {
                return true;
            }

            return false;
        }

        /**
         * Return the path array of the real requested path
         * @since 0.1.0
         *
         * @return array|mixed|string
         */
        private function getCurrentPathArray() {
            $dir = @parse_url($_SERVER ['REQUEST_URI'], PHP_URL_PATH);
            $dir = @trim($dir, '/');
            $dir = @explode('/', $dir);

            return $dir;
        }

        /**
         * Parse the required path of the resource
         * @since 0.1.0
         *
         * @param $dir
         * @return array|string
         */
        private function getGivenPathArray($dir) {
            $dir = @trim($dir, '/');
            $dir = @explode('/', $dir);

            return $dir;
        }
    }

}
?>