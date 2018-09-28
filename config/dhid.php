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
];
