# Api Sugar
> A library that allows for quick and easy API creation

This library uses [Flight](http://flightphp.com/) and [Eloquent](http://laravel.com/docs/5.1/eloquent) for routing and database interactions.  
You shouldn't need to look at Flight's api except for setup, but the Eloquent documentation is important to look at if you aren't aleady familiar. [Find it here](http://laravel.com/docs/5.1/eloquent)

## Usage:
#### Set up server routing
First you'll need to make sure that all urls reroute to your index.php file.  
  
For *Apache*, edit your `.htaccess` file with the following:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

For *Nginx*, add the following to your server declaration:

```
server {
    location / {
        try_files $uri $uri/ /index.php;
    }
}
```

### Include the project via composer

run `composer require calebdre/api-sugar`  
  
#### Create your endpoint class
Create a class that contains the endpoints and methods for the api:
```
class UserController extends ApiController{

    public $mappings = [
        'crud' => ['model' => 'Namespace\To\Model', 'resource_name' => 'users'],
	'fetchComment' => ['method' => 'get', 'route' => '/users/comment/@id'
    ];

    public function fetchComment($id){
	// do fetch comment stuff 
    }
}

```
Lets break class down:  
`class UserController extends ApiController`  
All endpoint classes need to extend the ApiController class.

`$mappings`  
This is the array that you can add to to create api endpoints. The structure is:  
`'methodName' => ['method' => 'get/post/put/delete', 'route' => '/end/point/path']`    
The method name is the name of the method in the class that should be called when the route is hit.  
  
You can also pass in `crud` to the array to scaffold CRUD route for an Eloquent model. the syntax for that is:  
`'crud' => ['model' => 'namespace/to/model/class', 'resource_name' => 'nameOfRoutePrefix']`  
  


#### Instatiate the API object and configure the database
```
	$api = new Api();
	$api->configureDB([
	    'driver'    => 'driver',
	    'host'      => 'host',
	    'database'  => 'database',
	    'username'  => 'username',
	    'password'  => 'pass',
	    'charset'   => 'utf8',
	    'collation' => 'utf8_unicode_ci',
	    'prefix'    => '',
	]);

	$api->addClass(new UserController());
	$api->execute();
```

You can add the class that you just created with the `addClass` method. To activate the api, use the `execute` method at the end.  
You can also use the `addEndPoint($methodType, $routeName, Callable $callable)` function to add routes manually.


After that you should have everything good to go!  
  
Please feel free to contribute to this project!


