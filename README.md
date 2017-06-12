## DreamHack Laravel SDK

### DHID Socialite service provider

The service provider will do two main things. It will add some database migrations and the following two routes: `/login/dhid` and `/login/dhid/callback`. 
When a user is logged in thru DHID it will automaticly be added to the `users` table and a reference to the DHID uuid is added.

#### Installation

After installing the DreamHack Laravel SDK, in your `config/app.php` configuration file add the following:

```php
'providers' => [
    // Other service providers...

    Laravel\Socialite\SocialiteServiceProvider::class,
	DreamHack\SDK\Providers\SocialiteServiceProvider::class,
],
```

Also, add the `Socialite` facade to the `aliases` array in your `app` configuration file:

```php
'aliases' => [
    // Other aliases
    'Socialite' => Laravel\Socialite\Facades\Socialite::class,
]
```

Configuration is normaly saved in the `.env` file. Add the following code in your `config/services.php` file to relay the parameters to the DHID Socialite service provider.

```php
    'dhid' => [
        'client_id' => env('DHID_CLIENT'),
        'client_secret' => env('DHID_SECRET'),
        'redirect' => env('DHID_REDIRECT')
    ],  
```

### Database

To run the database migrations added by the service provider run

```bash
artisan migrate
```

### Usage

Now almost everything is done. 
To login a user thu DHID, just send the user to the `/login/dhid` endpoint and the user will be redirected to the DHID login protal and then redirected back to your site when login is finished.
