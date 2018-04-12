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

2. Install `CookbookBundle` using composer:

    ```bash
    # execute in your eZ Platform project working directory:
    composer require ezsystems/cookbook-bundle:^1.0@dev
    ```
    
    Follow the instructions that show up on the screen to have fully working clean install of `CookbookBundle`.

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
    php bin/console cache:clear
    ```

5. Try already defined commands. You can find all available commands by:

   ```bash
   php bin/console |grep 'ezpublish:cookbook'
   ```

That's all!
