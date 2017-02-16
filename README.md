CookbookBundle
==============

This repository contains a eZ Publish 5.x and eZ Platform Cookbook Bundle, full of working examples for using eZ Publish 5.x and eZ Platform Public API.

# Getting started

Required tools:

- PHP (`7.0+` is recommended)
- Composer
- Git

1. Create and install eZ Platform using composer:

    ```bash
    composer create-project ezsystems/ezplatform
    ```

    Follow the instructions that show up on the screen to have fully working clean install of eZ Platform.

2. Clone (using git) `CookbookBundle` into your eZ Platform project:

    ```bash
    # execute in your eZ Platform project working directory:
    cd src
    # this directory is needed to generate autoload conforming to PSR-0
    mkdir EzSystems && cd EzSystems
    # clone `CookbookBundle`
    git clone git@github.com:ezsystems/CookbookBundle.git CookbookBundle
    cd ../..
    # dump composer autoloader
    composer dump-autoload
    ```

3. Enable the bundle in `AppKernel.php`:

    ```php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new EzSystems\CookbookBundle\EzSystemsCookbookBundle(),
        );

        // ...
    }
    ```

4. Clear Symfony cache

    ```bash
    php app/console cache:clear
    ```

5. Try already defined commands. You can find all available commands by:

   ```bash
   php app/console |grep 'ezpublish:cookbook'
   ```

That's all!
