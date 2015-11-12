Swagger Bundle for symfony 2
===========================


Installation
-----------

 - Install this bundle with composer!

```bash
composer require enneite/swagger-bundle
```

 - Edit your app/AppKernel.php to register the bundle in the registerBundles() method!

 - Define the swagger generator parameters in parameters.yml :

```yaml
swagger:
      config_file: '%kernel.root_dir%/config/swagger.json'
      routing: 'yaml' #yaml or annotation
      destination_bundle: 'MyAppBundle'
      destination_namespace: 'My\AppBundle'
```



Note:
-----
- **config_file parameter :** the json swagger configuration file
- **routing parameter     :** the type ou routing (yaml or annotation)
- **destination_bundle parameter :** the bundle where the API models and controllers must be generated
- **detination_namespace parameter :** the namespace used for the API PHP classes

If the "config_file" parameter is not define, the bundle will search a file named swagger.json in the config directory (app/config). The routing tye by default is yaml
The "destination" parameter is required.

Use
---

You generate the PHP classes for the API with a sf2 CLI :

```bash
php app/console swagger:generate
```

Controllers and swagger models:
------------------------------
"*Controller/Api*" directory and "*Api/Model*" directory will be created in your bundle source.
If you choose yaml config, an *"api_routing.yml"* file will be created in your bundle configuration file.

Security definitions:
--------------------
If you have defined security definitions in swagger, a file named "__api_security.yml.template" will be created in your "app/config" directory.  
  
**WARNING:** symfony2 security configuration have to be loaded in an unique file, the consequence is that you have to copy/paste the content of the "_api_security.yml.template" in your security.yml file.  
Authenticator services based on "Enneite\Swagger\Security\ApiAuthenticator" will be registered dynamically but you have to implement your own providers. To work, the attribute "name" and the attribute "in" have to be filled for each security definition.  

If your swagger file look like this:
```yaml
swagger: '2.0'
info:
  version: "1.0"
  title: My app
host: www.myapp.com
basePath: /api
security:
       - ep_auth:
         - application:all
         
securityDefinitions:
  app_auth:
    description: Authentication
    type: oauth2
    in: header
    name: Authorization
    flow: password
    scopes:
      application:all: Authentification for all paths in api
    tokenUrl: http://www.my-api-oauth-provider/oauth
```


Your security.yml contents have to look like this:

```yaml
security:

    providers:
        api_app_auth:
          id: app.user_provider # your user provider service ID (you have to implement it)
        in_memory:
            memory: ~

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt|error)|css|images|js)/
            security: false

        api_base_path:
              stateless: true
              pattern: ^/api
              simple_preauth:
                provider: api_app_auth
                authenticator: enneite_swagger.api_autenticator_app_auth #generated dynamically in swagger bundle

        main:
            anonymous: ~
```


 
 
 
  
  Note:
  ----
You can upgrade the API code too with the same command:

Models will be regenerated New controllers and new methods will be created.
Warning: If you choose yaml config, an *"api_routing.yml"* file will be regenaratedd in your bundle configuration file.
Warning :old controllers and methods will NOT BE removed!


Unit Tests
----------

Run unit tests like this:

```bash

./vendor/bin/phpunit ./Tests

```