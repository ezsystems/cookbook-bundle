CookbookBundle
==============

Cookbook bundle aims to:
- Provide full working examples of eZ Platform API use
- Serve as a reusale set of commands you can use when you need to during development

1.0 on this bundle aims to work across eZ Publish Platform 5.4 and eZ Platform 1.7 / 1.13 / 2.5.


# Getting started

Required:

- PHP _(minimum 5.6, 7.0+ is recommended)_
- Composer

0. Create and install eZ Platform using composer:

    ```bash
    composer create-project ezsystems/ezplatform:^2
    ```

    Follow the instructions that show up on the screen to have fully working clean install of eZ Platform.

1. Install `CookbookBundle` using composer:

    ```bash
    # execute in your eZ Platform project working directory:
    composer require ezsystems/cookbook-bundle:^1.0@dev
    ```
    
    Follow the instructions that show up on the screen to have fully working clean install of `CookbookBundle`.

2. Enable the bundle in `AppKernel.php`:

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

3. Clear Symfony cache

    ```bash
    php bin/console cache:clear
    ```

4. Try already defined commands. You can find all available commands by:

   ```bash
   php bin/console |grep 'ezpublish:cookbook'
   ```

That's all!
