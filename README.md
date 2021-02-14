# SupervisorBundle [![Build Status](https://secure.travis-ci.org/yzalis/SupervisorBundle.png)](http://travis-ci.org/yzalis/SupervisorBundle)

## About

This started out as a clone of YZSupervisorBundle, but since YZSupervisorBundle is no longer around I have modified it and updated it for the newest version of Symfony and PHP 7.4+. 

This bundle allows you to administer your Supervisor Programs on your Symfony Application with a nice Bootstrap UI!

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

``` code
composer require indydevguy/supervisor-bundle 
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

### Step 2: Enable the bundle

Then, enable the bundle by adding it to the list of registered bundles in the config/bundles.php file of your project (if it doesn't exist yet):

``` php
<?php
// config/bundles.php

return [
    // ...
    IndyDevGuy\Bundle\SupervisorBundle\IDGSupervisorBundle::class => ['all' => true],
    // ...
];
```

### Step 3: Configure your `config.yml` file

``` php
# app/config/config.yml
idg_supervisor:
    default_environment: dev
    servers:
        prod:
            SUPERVISOR_01:
                host: 192.168.0.1
                username: guest
                password: password
                port: 9001
            SUPERVISOR_02:
                host: 192.168.0.2
                username: guest
                password: password
                port: 9001
        dev:
            locahost:
                host: 127.0.0.1
                username: guest
                password: password
                port: 9001
                groups: ['example_site']
```

The group option limits access to specific process groups. When no groups are provided, all groups are listed.

# Usage

Iterate over all supervisor servers:
``` php
$supervisorManager = $this->container->get('supervisor.manager');

foreach ($supervisorManager->getSupervisors() as $supervisor) {
    echo $supervisor->getKey();
    // ...
}
```

Retrieve specific supervisor servers:
```
$supervisorManager = $this->container->get('supervisor.manager');

$supervisor = $supervisorManager->getSupervisorByKey('uniqueKey');

echo $supervisor->getKey();
```

# User interface

You can access to a beautiful user interface to monitor all your supervisor servers an process.

Import the routing definition in `routing.yml`:
``` yaml
# app/config/routing.yml
IDGSupervisorBundle:
    resource: "@IDGSupervisorBundle/Resources/config/routing.yaml"
    prefix: /supervisor
```

Here is the result

![Supervisor Bundle screenshot](https://github.com/yzalis/SupervisorBundle/raw/master/Resources/doc/SupervisorBundle-1.png)

# Unit Tests

To run unit tests, you'll need a set of dependencies you can install using Composer:
```
php composer.phar install
```

Once installed, just launch the following command:
```
phpunit
```

You're done.

## Credits

* Benjamin Laugueux <benjamin@yzalis.com>
* IndyDevGuy
* [All contributors](https://github.com/yzalis/SupervisorBundle/contributors)

## License

Supervisor is released under the MIT License. See the bundled LICENSE file for details.
