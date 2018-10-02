<?php

return [
    /**
     * The base URL for the API, probably only overwritten by dev environment
     */
    "api_base_url" => env('API_BASE_URL', 'https://api.dreamhack.com'),

    /**
     * Client ID
     */
    "api_client_id" => env('API_CLIENT_ID'),

    /**
     * API Secret
     */
    "api_secret" => env('API_SECRET'),

    /**
     * The base URL for when we connect to our own API, in dev environment this
     * connects straight to the "api" docker container, eg. http://api (usually)
     */
    "api_internal_base_url" => env('API_INTERNAL_BASE_URL', 'https://api.dreamhack.com'),

    /**
     * The base URL for external connections (eg. browsers) to connect to our API
     */
    "api_external_base_url" => env('API_EXTERNAL_BASE_URL', 'https://api.dreamhack.com'),

    /**
     * Another part of the URLs for the API, the name of the service usually. eg. internal, content etc.
     */
    "api_prefix" => env('API_PREFIX'),

    /**
     * Lets the API proxy know it's connected to the right service. Needs to match what it expects.
     */
    "api_uuid" => env('API_UUID'),

    /**
     * OAuth settings for clients like internal, guestlist
     */
    "dhid_client" => env('DHID_CLIENT'),
    "dhid_redirect" => env('DHID_REDIRECT'),
    "dhid_secret" => env('DHID_SECRET'),

    /**
     * Where to connect to reach the socket service
     */
    "socket_base_url" => env('SOCKET_BASE_URL'),

    /**
     * This is a weird one, but it also doesn't matter. Leave it as dev even for prod.
     */
    "version" => env('VERSION', 'dev'),

    /**
     * The main url for dreamhack.com. Use this, append your path and it will work fine in dev and prod
     */
    "dhcom_url" => env('DHCOM_URL', 'https://dreamhack.com/'),

    /**
     * URL to the CDN
     */
    "filebank_url" => env('FILEBANK_URL', 'https://cdn.dreamhack.com/'),

    /**
     * The IP of the API Proxy, used to allow it to do certain things, make sure it's correct
     */
    "proxy_ip" => env('PROXY_IP', '127.0.0.1'),

    /**
     * Mandrill API key
     */
    "mandrill_api_key" => env('MANDRILL_API_KEY'),
];
