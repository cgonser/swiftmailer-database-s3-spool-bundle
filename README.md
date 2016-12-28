# Swift Mailer Database S3 Spool

A Symfony bundle that enables Swift Mailer to spool messages from a database and store message files on an Amazon S3 bucket.

It requires the [AWS PHP SDK](https://github.com/aws/aws-sdk-php) and relies on Doctrine for data persistency.

## Installation

This bundle can be installed via Composer by requiring `cgonser/swiftmailer-database-s3-spool-bundle package` in your project's composer.json:

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

Still in `app/config/config.yml`, enable the services and change the swift mailer spool configuration:

```yaml
imports:
    // ...
    - { resource: "@CgonserSwiftMailerDatabaseS3SpoolBundle/Resources/config/services.yml" }
```

```yaml
swiftmailer:
    // ...
    spool: { type: db_s3 }
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

After finishing the configuration, you will need to update your database schema to create the entity necessary to store the spooler queue.

```console
php bin/console doctrine:schema:update
```

## Mail Queue Entity

By default, the mail queue will be stored in a table named cgonser_mail_queue, but you can override the default entity. To do so, you will need to create a new entity with the same structure of the default one (which you can find inside the package at `Entity/MailQueue.php`) and change its name and/or definition.

After that, you will need to inform the bundle about the new entity, using the following configuration in `app/config/config.yml`:

```yaml
cgonser_swift_mailer_database_s3_spool:
    entity_class: "<YOUR NEW ENTITY>" (e.g.: \AppBundle\Entity\MailQueue)
```

Keep in mind that this bundle relies on the default entity structure and modifying that may break it.

