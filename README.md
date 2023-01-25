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

## Events

The `EntityController` calls various overridable methods. These are:

1. `entityPostFormBuild`
1. `entityPreValidate`
1. `entityPreSave`
1. `entityPostSave`

It may be tempting to put certain logic here. Keep in mind, these methods are
only called for the create/update actions through your entity controller. The
logic should only specifically apply to the controller.

If you need certain things to happen no matter where your entity is saved, you
should be hooking into Doctrine Events.

If you need common logic for both your controller and your event subscriber, you
can create common functions in your entity provider. The provider is already
available in your controller, and can be injected into your subscriber.

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
{{ entity_action(action, entity, route, label, attributes) }}
```

These will only work if the value for 'action' is the same in both
the route AND the voter.

## Rendering Entity Create Links

Determining if a 'create' link should be shown requires some setup,
because you need to have an entity to pass to the voter.
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
