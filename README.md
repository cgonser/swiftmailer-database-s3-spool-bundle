# Swift Mailer Database S3 Spool

A Symfony bundle that enables Swift Mailer to spool messages from a database and store message files on an Amazon S3 bucket

It requires the [AWS PHP SDK](https://github.com/aws/aws-sdk-php) and the [AWS Service Provider Bundle](https://github.com/aws/aws-sdk-php-symfony) and relies on Doctrine for data persistency.

## Installation

This bundle can be installed via Composer by requiring cgonser/swiftmailer-database-s3-spool-bundle package in your project's composer.json:

```json
{
    "require": {
        "cgonser/swiftmailer-database-s3-spool-bundle": "dev-master"
    }
}
```

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...

            new Cgonser\SwiftMailerDatabaseS3SpoolBundle\CgonserSwiftMailerDatabaseS3SpoolBundle(),
        ];

        // ...
    }

    // ...
}
```

## Configuration

Please remember to first configure the AWS SDK and AWS Service Provider accordingly. Once they are properly configured, you can place this bundle configuration in `app/config/config.yml` file.

```yaml
cgonser_swift_mailer_database_s3_spool:
    s3_bucket: "<< TARGET BUCKET >>"
```
