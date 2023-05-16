# Installation

Make sure `ohmediaorg/timezone-bundle` is set up.

Enable the bundle in `config/bundles.php`:

```php
return [
    // ...
    OHMedia\SecurityBundle\OHMediaSecurityBundle::class => ['all' => true],
];
```

## User Files

Create your user class using the boilerplate command and the user flag:

```bash
php bin/console ohmedia:security:boilerplate --user
```

This will generate all the Entity classes for `App\Entity\User`, including a
command for creating your first user. Update this as needed.

### User Login

Follow the steps at https://symfony.com/doc/current/security.html#form-login for
creating a login form.

## Config

Update `config/packages/doctrine.yml`:

```yaml
doctrine:
  # ...
  orm:
    # ...
    resolve_target_entities:
        OHMedia\SecurityBundle\Entity\User: App\Entity\User
```

Update `config/packages/security.yml`:

```yaml
security:
    # ...

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
    firewalls:
        # ...
        main:
            # ...
            provider: app_user_provider
```

## Migrations

Add custom fields to your user class using the maker command:

```bash
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. TinyGnome):
 > User
```

Make the migration:

```bash
$ php bin/console make:migration
```

Before running the migration, you will need to make sure the migration generated
by the boilerplate is updated as needed.

```bash
$ php bin/console doctrine:migrations:migrate
```

## First User

To create the first user, run the command that was generated with the rest
of the User files.

```bash
$ php bin/console app:user:create
```

You may need to update this command depending on the custom fields you added
to your User entity.

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

Create a new voter for the custom attribute:

```php
<?php

namespace App\Security\Voter\Post;

use App\Entity\Post;
use OHMedia\SecurityBundle\Entity\User as EntityUser;
use OHMedia\SecurityBundle\Security\Voter\SingleAttributeVoter;

class PostPublishVoter extends SingleAttributeVoter
{
    protected function getSubjectType(): string
    {
        return Post::class;
    }

    protected function voteOnSubject($post, EntityUser $loggedIn): bool
    {
        return $post->isApproved() && !$post->isPublished();
    }
}
```

Utilize the custom attribute, like in a controller:

```php
// App/Controller/PostController.php

use App\Security\Voter\Post\PostPublishVoter;

// ...

#[Route('/post/{id}/publish', name: 'post_publish', methods: ['GET', 'POST'])]
public function publish(Post $post, Request $request)
{
    $this->denyAccessUnlessGranted(
        PostPublishVoter::class,
        $post,
        'You cannot publish this post.'
    );
    
    // ...
}
```
