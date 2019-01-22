# wordpress-bitbucket-trigger-pipeline

WordPress Plugin which triggers a Bitbucket Pipeline when user publishes a post.
Ideal for static generation tasks.

For use with:

* WordPress
* Bitbucket Pipelines

## Installation

Clone the repository to your WordPress plugins folder at:

    /wp-content/plugins/wordpress-bitbucket-trigger-pipeline/

Activate the plugin via the WordPress interface at:

    http://localhost:8080/wp-admin/plugins.php

Go to your Bitbucket project and generate an App Password using the guide:

    https://blog.bitbucket.org/2016/06/06/app-passwords-bitbucket-cloud/

Set the Bitbucket variables in the 'Settings' > 'General' section on WordPress,
using your Bitbucket project and branch.

    BITBUCKET PROJECT: XX
    BITBUCKET BRANCH: XX

The branch needs to be defined in your Bitbucket Pipelines configuration.

Edit `wp-config.php` and add define the following variables:

```php
define( 'BITBUCKET_USERNAME', '<username>' );
define( 'BITBUCKET_PASSWORD', '<password>' );
```

## Usage

Go to your WordPress Admin for posts:

    http://localhost:8080/wp-admin/edit.php

Publish an existing or new post.

## Deployment

Go to your WordPress Admin and open Deploy page:

    http://localhost:8080/wp-admin/admin.php?page=test-button-slug

Click the **Deploy to Production** button and then check Bitbucket that your
pipeline was triggered.

Note that the button always says **Production**.

## Directory structure

    bitbucket-trigger-pipeline.php   --> WordPress Plugin Code
    readme.txt                       --> WordPress Plugin Readme
    readme.md                        --> This file

## Contact

For more information please contact [kmturley](https://github.com/kmturley)
