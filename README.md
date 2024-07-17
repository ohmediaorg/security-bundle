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

## Admin Permissions

Editing an admin user will show a selection of Permissions. To add to this
selection, create a service that implements `OHMedia\SecurityBundle\Service\EntityChoiceInterface`.

You may need to manually tag your service as `oh_media_security.entity_choice`.

# Custom User Type

Start by creating an entity to represent your custom user type (eg. `Member`).

Add a `OneToOne` relationship from `Member` to `User`. Add any fields to `Member`
that aren't represented in the `User` entity (eg. `phone`).

The `User` entity attached to the `Member` should have a custom value for `type`
(recommendend to use the value of `Member::class`) and the value for `entities`
should be populated accordingly. Leave `entities` as a blank array if the Member
should only be allowed to log in and view locked content.

The `Member` entity is intended to be separate from the `User` entity as far as
UI goes. There should be entirely separate routes (ie. `member_index`,
`member_create`, `member_edit`, `member_delete`, etc.). `User` entities with
custom `type` values will be excluded from the regular `User` routes.

## User Type Forms

Create a `MemberUserType` form for handling `User` data under the `Member`:

```php
$builder->add('phone', TelType::class);
$builder->add('user', MemberUserType::class);
```

The form can be custom rendered to seamlessly merge the two:

```twig
{{ form_start(form) }}
  {{ form_row(form.user.first_name) }}
  {{ form_row(form.user.last_name) }}
  {{ form_row(form.user.email) }}
  {{ form_row(form.phone) }}
{{ form_end(form) }}
```

Create a higher priority route for `user_profile` that will display a custom
form for this user type and otherwise forward to the `ProfileController`.

## User Type Permissions

Let's say a `Member` was allowed to create `Article` entities. You would need to
make sure the `entities` value of the associated `User` was set as follows:

```php
$user = new User();
$user->setType(Member::class);
$user->setEntities([Article::class]);

$member = new Member();
$member->setUser($user);
```

Then you can create a custom `ArticleVoter` will logic like the following:

```php
protected function canEdit(Article $article, User $loggedIn)
{
    if ($loggedIn->isType(Member::class)) {
        // make sure they are the "owner" of this Article
    }
}
```

Instead of trying to override the existing Article listing, you could create a
custom listing separate from that which lists out a particular member's articles:

```php
#[Route('/member/{id}/articles', name: 'member_articles', methods: ['GET'])]
public function articles(Member $member)
{
    // get articles that the Member "owns"
}
```

This would require a secondary table to associate `Member` to `Article`.
