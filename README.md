# Installation

Enable the bundle in `config/bundles.php`:

```php
return [
    // ...
    OHMedia\SecurityBundle\OHMediaSecurityBundle::class => ['all' => true],
];
```

Create your user class using the boilerplate command and the user flag:

```bash
php bin/console ohmedia:security:boilerplate --user
```

_**Note:** the rest of the installation instructions assume you will call your user
class `User`._

For every login form you need, extend
`OHMedia\SecurityBundle\Security\AbstractUserAuthenticator`.

```php
<?php

namespace App\Security;

use App\Entity\User;
use OHMedia\SecurityBundle\Security\AbstractUserAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LoginAuthenticator extends AbstractUserAuthenticator
{
    protected function getLoginRoute()
    {
        return 'login';
    }
    
    protected function getLoginSuccessRoute(TokenInterface $token)
    {
        return 'home';
    }
    
    protected function getUserClass()
    {
        return User::class;
    }
}

```

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

Add custom fields to your user class using the maker command:

```bash
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. TinyGnome):
 > User
```

Make and run the migration:

```bash
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```

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
