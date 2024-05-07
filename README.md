# Installation

Update `composer.json` by adding this to the `repositories` array:

```json
{
    "type": "vcs",
    "url": "https://github.com/ohmediaorg/security-bundle"
}
```

Then run `composer require ohmediaorg/security-bundle:dev-main`.

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
                username_parameter: form[email]
                password_parameter: form[password]
                csrf_parameter: form[token]

            logout:
                path: user_logout
                target: user_login

            login_throttling: ~
    # ...

    access_decision_manager:
        strategy: unanimous
        allow_if_all_abstain: false
```

Update `config/packages/routes.yml`:

```yaml
oh_media_security:
    resource: '@OHMediaSecurityBundle/config/routes.yaml'
```

## Migrations

Make the user migrations:

```bash
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```

## First User

To create the first user, run this command:

```bash
$ php bin/console ohmedia:security:create-user
```

## Custom Attributes

Define a new attribute constant and corresponding function in your voter:

```php
<?php

namespace App\Security\Voter;

use App\Entity\Post;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;

class PostVoter extends AbstractEntityVoter
{
    // ...
    const PUBLISH = 'publish';

    // ...

    protected function canPublish(Post $post, User $loggedIn): bool
    {
        return !$post->isPublished();
    }
}
```

The corresponding function is "can" concatenated with the PascalCase of the
attribute string. In this case, "publish" and "canPublish".

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

## User Permissions

Editing a non-developer user will show a selection of Permissions. To add to this
selection, create a service that implements `OHMedia\SecurityBundle\Service\EntityChoiceInterface`.

You may need to manually tag your service as `oh_media_security.entity_choice`.
