<?php
namespace System\Core;

class Proxy {
    public function __construct($path) {
        // Set the target URL you want to proxy
        $targetUrl = $path[1] ? implode('/', array_slice($path, 1)) : exit('No target specified');

        // Create a new cURL resource
        $curl = curl_init();

        // Set the options for the cURL request
        curl_setopt($curl, CURLOPT_URL, $targetUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Use this only for testing, not recommended in production

        $cookies = $_SERVER["HTTP_COOKIE"]??'';
        $headers = [];
        if ( !empty($cookies) ) $headers = ["Cookie: $cookies"];

        $isPosted = false;
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            // Get the POST payload from the request
            $headers[] = 'Content-Type: ' . $_SERVER['CONTENT_TYPE'];
            $payload = file_get_contents('php://input');
            foreach ( explode(';', $cookies) as $cookie ) {
                if ( strpos($cookie, "MoodleSession=") !== false ) {
                    $headers[0] = "Cookie: $cookie";
                    break;
                }
            }
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            $isPosted = true;
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // Execute the cURL request
        $response = curl_exec($curl);

        // Check for errors
        if ( $response === false ) exit('Error: ' . curl_error($curl));

        // Get the response headers
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseHeaders = substr($response, 0, $headerSize);

        // Output the response headers
        header('Content-Type: ' . curl_getinfo($curl, CURLINFO_CONTENT_TYPE));

        // Close the cURL resource
        curl_close($curl);

        // Filter the 'Set-Cookie' header response to the client
        preg_match_all('/^Set-Cookie:\s*([^;]*)(;\s*secure)?/mi', $responseHeaders, $matches);
        if (!empty($matches[1])) {
            $cookies = $matches[1];
            $cookieValues = [];

            // Filter and store the values of MoodleSession cookies
            foreach ($cookies as $cookie) {
                if (stripos($cookie, "MoodleSession=") !== false) {
                    $cookieValue = substr($cookie, strpos($cookie, "=") + 1);
                    $cookieValues[] = $cookieValue;
                } else {
                    header("Set-Cookie: $cookie; path=/", false);
                }
            }

            // Set the first occurrence of MoodleSession cookie
            if (!empty($cookieValues)) {
                $firstCookieValue = reset($cookieValues);
                header("Set-Cookie: MoodleSession=$firstCookieValue; path=/", false);
            }
        }

        // Output the response body content
        $responseBody = substr($response, $headerSize);
        return exit($responseBody);
    }
}
