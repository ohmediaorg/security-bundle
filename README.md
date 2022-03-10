# Installation

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

This will generate all the Entity classes for `App\Entity\User`, as well as some
additional files needed to kickstart logging in.

The file `templates/security/login.html.twig` will contain the minimum form
needed for logging in. Feel free to style this as needed. The important part is
making sure the 3 inputs are named `email`, `password`, and `_csrf_token`.

The file `App/Controller/LoginController.php` will handle displaying the login
form to the user. The submission and redirection of this form is handled by
`App/Security/LoginAuthenticator.php`.

The authenticator and controller will use predetermined routes. Feel free
to change these.

The last thing generated is a migration for creating your first user. This will
be addressed later.

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
    encoders:
        App\Entity\User:
            algorithm: auto

    providers:
        oh_media_security_user_provider:
            entity:
                class: App\Entity\User
                property: email
    firewalls:
        # ...
        main:
            # ...
            provider: oh_media_security_user_provider
            guard:
                authenticators:
                    - App\Security\LoginAuthenticator
```

Override the default timezone `config/packages/oh_media_security.yml`:

```yaml
oh_media_security:
    timezone: America/Regina # this is the default
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

To create the first user, generate an empty migration. Update it as follows:

1. Rename the migration file and class so it is always guaranteed to run last.
In other words, change `Version20221025053121` to `Version21221025053121`.

1. Add these includes:

```php
use App\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
```

1. Make the migration implement the ContainerAwareInterface interface:

```php
final class Version21221025053121
extends AbstractMigration
implements ContainerAwareInterface
```

1. Add the $container property and setter:

```php
private $container;

public function setContainer(ContainerInterface $container = null)
{
    $this->container = $container;
}

public function getDescription() : string
{
    return '';
}
```

1. Finally, implement the postUp function:

```php
public function postUp(Schema $schema) : void
{
    $em = $this->container->get('doctrine.orm.entity_manager');
    $encoder = $this->container->get('security.password_encoder');
    
    $user = new User();
    
    // set the default password to something easy
    // with the intent to change it immediately after
    $encoded = $encoder->encodePassword($user, '123456');
    
    $user
        // set the email you want to log in with
        ->setEmail('email@website.com')
        ->setPassword($encoded)
        // set other fields as needed
    ;
    
    $em->persist($user);
    $em->flush();
}
```

The up and down functions should remain empty.

After this migration is ran, you can log in with the user you created.

# Entities

Create your other entities class using the boilerplate command with no flag:

```bash
php bin/console ohmedia:security:boilerplate

 Class name of the entity:
 > MyEntity
```

then add your custom fields using the maker command:

```bash
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. TinyGnome):
 > MyEntity
```

You may want to represent some of these custom fields in the
`App\Form\MyEntityType` class that was auto-generated.

## Custom Actions

Add the custom action to your provider:

```php
// App/Provider/MyEntityProvider.php

public function getCustomActions(): array
{
    $actions = [
        'my-custom-action',
    ];
    
    return $actions;
}
```

By convention, this string should be kebab case.

If you are using the default routes, you will need to handle this action in your
controller. The name of the controller function should match the camel-cased of
your action string appended by the word "Action".

```php
// App/Controller/MyEntityController.php

public function myEntityActionAction(Request $request)
{
    $this->preActionSetup($request, 'my-entity-action');
    
    // preActionSetup() will call all the "voters" in the system
    
    // if preActionSetup() is successful, the following are available:
    // $this->em - the entity manager
    // $this->request - the current Request
    // $this->user - the currently logged-in user
    // $this->entity - the entity the action is being performed on
    // $this->provider - your Provider class
    
    // do stuff
    
    return $this->render(...);
}
```

Manage the permissions for this custom action in your voter. The name of the
voter function should match the pascal-case of your action string prepend by
the word "can".

```php
// App/Security/Voter/MyEntityVoter.php

protected function canMyCustomAction(MyEntity $myEntity, User $loggedIn)
{
    // return true or false
}
```

## Locking

An entity can become lockable if it uses the trait
`OHMedia\SecurityBundle\Entity\Traits\Lockable`. By default, this happens on the
`update` action in order to prevent two people from updating the same thing.

Check out how the `EntityController` utilizes the `LockingController` trait.

# Template Helpers

## Rendering Entity Action Links 

You can use twig helpers for rendering action links on existing entities.
Links are only rendered if the voting passes.

```twig
{{ oh_media_entity_action(action, entity, route, label, attributes) }}
```

These will only work if the value for 'action' is the same in both
the route AND the voter.

## Rendering Entity Create Links

Determining if a 'create' link should be shown requires some setup,
because you need to create an entity so it can be passed to the voter.
Just make sure not to persist that new entity!

```php
<?php

use App\Provider\MyOtherEntityProvider;

class MyEntityController extends EntityController {
  
  // ...
  
  public function someOtherAction(Request $request, OtherEntityProvider $otherEntityProvider)
  {
      // ...
      
      // to create a new entity matching this controller
      $new_my_entity = $this->getEntityNew();
      
      // to create a new entity unrelated to this controller
      // you can autowire its provider into the action
      $new_other_entity = $otherEntityProvider->create();
      
      /// ...
      
  }
}
```

then in the template:

```twig
{% if is_granted('create', new_entity) %}
  {# render a link using the create route for this entity #}
{% endif %}

{% if is_granted('create', new_other_entity) %}
  {# render a link using the create route for this entity #}
{% endif %}
```
