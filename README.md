# REST in PHP - RIP

### Content
* Installation
* Set up
* Examples
* Tips
* License

## Installtion
Install RIP with Composer: 
````composer require racler-productions/rip````

## Set up
First of all you need to Initialize the Rip class ``$rip = new Rip();``
Then you can  access the resource methods you want. Make sure that you have set up an htaccess file that forwards the requests to the application. For example: 

````
DirectoryIndex index.php

RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]

<IfModule mod_rewrite.c>
RewriteEngine on
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
</IfModule>
````

## Examples

#### GET
````
$rip->get("/user/:name/profile/picture", array("name"), $authFilter, function($parameters){
    echo 'Welcome ' . $parameters["name"];
});
````
Path parameters are marked with a colon ````:````.

#### POST
````
$rip->post("/user/profile/picture", array("imageUrl", "userId"), $authFilter, functions($parameters){
    //Handle the request
});
````

#### PUT
````
$rip->put("/user/profile/picture", array("imageUrl", "userId"), $authFilter, functions($parameters){
    //Handle the request
});
````

#### DELETE
````
$rip->put("/user/profile/picture", array("userId"), $authFilter, functions($parameters){
    //Handle the request
});
````

#### Filter
The filter is a function that is called before the actual function is executed. 
A simple authentication filter could look like this:
````
$authFilter = function(){
    $token = //get token from header
    if($this->tokenIsVald($token)){
        return $token
    }else{
        //Handle invalid token
    }
};
````

## Tips
* Resources that are often used are best placed at the beginning of the application.
* No other output before usage.
* After completion of each RIP function, prevent further execution.


## License
Copyright 2018 Racler Productions

MIT, see LICENSE for details
