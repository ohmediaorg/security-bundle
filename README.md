# Installation

Make sure `ohmediaorg/timezone-bundle` is set up.

Enable the bundle in `config/bundles.php`:

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
```

TODO: steps for user login

Follow the steps at https://symfony.com/doc/current/security.html#form-login for
creating a login form.

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
