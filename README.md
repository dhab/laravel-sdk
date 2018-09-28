## DreamHack Laravel SDK

### DHID Socialite service provider
The service provider will do two main things. It will add some database migrations and the following two routes: `/login/dhid` and `/login/dhid/callback`. 
When a user is logged in thru DHID it will automaticly be added to the `users` table and a reference to the DHID uuid is added.

#### Installation
After installing the DreamHack Laravel SDK, in your `config/app.php` configuration file add the following:

```php
'providers' => [
    // Other service providers...

	DreamHack\SDK\Providers\SocialiteServiceProvider::class,
],
```
Run `php artisan vendor:publish --provider="DreamHack\SDK\Providers\DHIDServiceProvider"` to get a template config and change values in there, or in your .env file.

### Database
To run the database migrations added by the service provider run

```bash
artisan migrate
```

### Usage
Now almost everything is done. 
To login a user thu DHID, just send the user to the `/login/dhid` endpoint and the user will be redirected to the DHID login protal and then redirected back to your site when login is finished.

### Views
Run the following command to copy the views in to your `resources/views/vendor` folder. This way you can customize the default views as you like.

```bash
php artisan vendor:publish --provider="DreamHack\SDK\Providers\SocialiteServiceProvider"
```
