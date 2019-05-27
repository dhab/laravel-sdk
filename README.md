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

# Getting started with DHResource
DHResource is our way to automagically create CRUD-endpoints that suits our admin interface.

## Controller setup

### Namespaces
The following namespaces needs to be "imported" in the controller.

```php
use DreamHack\SDK\Http\Resource;
use App\Models\Foo;
```

### Annotations
Make sure annotations is set up correctly and then but this phpdoc above the controller class:

This is the most basic version:

```php
/**
 * @DHResource(
    "<path/to/base>",
    version="1"
    as="<modelname>"
   )
 * @Super
 */
```

@Super indicates that "all" methods will require super user privileges.
The alternative is @SkipAuth, but that's not really useful for admin CRUD :)

### You can can also add these options to @DHResource and @Super:

 * only

   If you need to override a certain method, you can configure what methods you need like:

   ```php
   @DHResource(only={"index", "store", "show", "update", "destroy"})
   ```

 * except

   If some resources don't need to be for super users only

   ```php
   @Super(except={"publicGet"})
   ```

### Mandatory functions in controller
A DHResource controller usually starts out like this:

```php
class FooController extends Controller
{
    use Resource;
    protected static function getClass()
    {
        return Foo::class;
    }
```

### Abstract model methods in Resource trait

 * getDefaultRelations()

   return a list of relations to send to `$model->with()` / `->load()`

 * getSyncRelations()

   return a list of relations to send to `$model->sync()`

 * getResponseClass()

   if the response needs to be special, return the name of a class that implements `DreamHack\SDK\Http\Responses\ModelResponse`

 * getRequiredFields()

   list of fields that are mandatory

 * getFieldValidators()

   return validators for each field in the model

 * getEventsAffected()

   return a list of event-IDs to clear proxy cache for, usually just runs the same function in the connected event.
