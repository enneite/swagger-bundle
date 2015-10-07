Swagger Bundle for symfony 2
===========================


Installation
-----------

 - Install this bundle with composer!

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
"*Controller/Api*" directory and "*Api/Model*" directory will be created in your bundle source.
If you choose yaml config, an *"api_routing.yml"* file will be created in your bundle configuration file.

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