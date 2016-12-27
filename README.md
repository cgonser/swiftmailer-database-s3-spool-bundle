# Swift Mailer Database S3 Spool

A Symfony bundle that enables Swift Mailer to spool messages from a database and store message files on an Amazon S3 bucket

It requires the [AWS PHP SDK](https://github.com/aws/aws-sdk-php) and relies on Doctrine for data persistency.

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

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...

            new Cgonser\SwiftMailerDatabaseS3SpoolBundle\CgonserSwiftMailerDatabaseS3SpoolBundle(),
        ];
    }
}
```

## Configuration

Please remember to first configure the AWS SDK accordingly. Once it's  properly configured, you can place this bundle configuration in `app/config/config.yml` file.

```yaml
cgonser_swift_mailer_database_s3_spool:
    s3:
        bucket: "<TARGET BUCKET>"
        region: "<S3 REGION>"
```

You can also provide specific AWS credentials for this bucket, if you want to:

```yaml
cgonser_swift_mailer_database_s3_spool:
    s3:
        bucket: "<TARGET BUCKET>"
        region: "<BUCKET REGION>"
        key: "<YOUR AWS KEY>"
        secret: "<YOUR AWS SECRET>"
```

