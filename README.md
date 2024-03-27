# Installation

Make sure the following bundles are installed and set up:

1. `ohmediaorg/antispam-bundle`
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
                username_parameter: form[email]
                password_parameter: form[password]
                csrf_parameter: form[token]

            logout:
                path: user_logout
                target: user_login

    # ...

    access_decision_manager:
        strategy: unanimous
        allow_if_all_abstain: false
```

Update `config/packages/routes.yml`:

```yaml
oh_media_security:
    resource: '@OHMediaSecurityBundle/Controller/'
    type: annotation
```

### Login Throttling

https://symfony.com/blog/new-in-symfony-5-2-login-throttling

## Templates

Override this bundle's templates in the directory `templates/bundles/OHMediaSecurityBundle`.

### Forms

You will need to render some forms by creating the following files in the
aforementioned directory:

1. `forgot_password_form.html.twig`
1. `login_form.html.twig`
1. `password_reset_form.html.twig`

The forms can simply be rendered with `{{ form(form) }}`.

### Emails

Email templates can be overridden in the same directory:

1. `password_reset_email.html.twig`
1. `verification_email.html.twig`

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
