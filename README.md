# Installation

Make sure the following bundles are installed and set up:

1. `ohmediaorg/email-bundle`
1. `ohmediaorg/timezone-bundle`
1. `ohmediaorg/utility-bundle`

Enable the security bundle in `config/bundles.php`:

```php
return [
    // ...
    OHMedia\SecurityBundle\OHMediaSecurityBundle::class => ['all' => true],
];
```

## Config

Update `config/packages/security.yml`:

```yaml
security:
    # ...

    providers:
        app_user_provider:
            entity:
                class: OHMedia\SecurityBundle\Entity\User
                property: email
    firewalls:
        # ...
        main:
            # ...
            provider: app_user_provider
            
            form_login:
                login_path: user_login
                check_path: user_login
                enable_csrf: true
                username_parameter: form[_username]
                password_parameter: form[_password]
                csrf_parameter: form[_csrf_token]

            logout:
                path: user_logout
                target: user_login
```

Update `config/packages/routes.yml`:

```yaml
oh_media_security:
    resource: '@OHMediaSecurityBundle/Controller/'
    type: annotation
```

You will need to render the login form by creating the file
`templates/bundles/OHMediaSecurityBundle/login.html.twig`.

The form itself can be rendered with `{{ form(form) }}`.

## Migrations

Make the user migrations:

```bash
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```

## First User

To create the first user, run the command that was generated with the rest
of the User files.

```bash
$ php bin/console ohmedia:security:create-user
```

# Entities

Create your other entity classes using the boilerplate command with no flag:

```bash
php bin/console ohmedia:security:boilerplate

 Class name of the entity:
 > Post
```

then add your custom fields using the maker command:

```bash
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. TinyGnome):
 > Post
```

You may want to represent some of these custom fields in the
`App\Form\PostType` class that was auto-generated.

## Custom Attributes

Define a new attribute constant and corresponding function in your voter:

```php
<?php

namespace App\Security\Voter;

use App\Entity\Post;
use OHMedia\SecurityBundle\Entity\User as EntityUser;
use OHMedia\SecurityBundle\Security\Voter\EntityVoter;

class PostVoter extends EntityVoter
{
    // ...
    const PUBLISH = self::ATTRIBUTE_PREFIX . 'publish';
    
    // ...

    protected function canPublish(Post $post, EntityUser $loggedIn): bool
    {
        return !$post->isPublished();
    }
}
```

Here, the suffix is "publish" and the corresponding function is `canPublish`.

If you had `const APPROVE_ALL = self::ATTRIBUTE_PREFIX . 'approve_all';`, the
corresponding function would be `canApproveAll` because of the suffix "approve_all".

## Voter Attribute Constants

Utilizing voter constants in a controller:

```php
// App/Controller/PostController.php

use App\Security\Voter\PostVoter;

// ...

#[Route('/post/{id}/publish', name: 'post_publish', methods: ['GET', 'POST'])]
public function publish(Post $post, Request $request)
{
    $this->denyAccessUnlessGranted(
        PostVoter::PUBLISH,
        $post,
        'You cannot publish this post.'
    );
    
    // ...
}
```

Utilizing voter constants in a template:

```twig
{% set publish_attribute = constant('App\\Security\\Voter\\PostVoter::PUBLISH') %}

{% if is_granted(publish_attribute, post) %}
    {# do something #}
{% endif %}
```
