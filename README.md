Installation
============

Enable the bundle in `config/bundles.php`:

```php
return [
    // ...
    OHMedia\SecurityBundle\OHMediaSecurityBundle::class => ['all' => true],
];
```

For every login form you need,
extend `OHMedia\SecurityBundle\Security\AbstractUserAuthenticator`.

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

Create your user class:

```php
<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\User as EntityUser;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends EntityUser
{
}
```

and your User repository:

```php
<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
}
```

then add your custom fields using the maker command:

```bash
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. TinyGnome):
 > User
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
        ohmedia_security_user_provider:
            entity:
                class: App\Entity\User
                property: email
    firewalls:
        # ...
        main:
            # ...
            provider: ohmedia_security_user_provider
            guard:
                authenticators:
                    - App\Security\LoginAuthenticator
```

Override the default timezone `config/packages/ohmedia_security.yml`:

```yaml
ohmedia_security:
    timezone: America/Regina # this is the default
```

Make and run the migration:

```bash
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```

Entity Management
=================

Entity
------

If you are working on your User entity, skip this step.

Create your entity's class:

```php
<?php

namespace App\Entity;

use App\Repository\MyEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Entity;

#[ORM\Entity(repositoryClass: MyEntityRepository::class)]
class MyEntity extends Entity
{
    // optionally, use this trait to enable locking
    // (on the 'update' action by default)
    use \OHMedia\SecurityBundle\Entity\Traits\Lockable;
}
```

and your entity's repository:

```php
<?php

namespace App\Repository;

use App\Entity\MyEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyEntity[]    findAll()
 * @method MyEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyEntity::class);
    }
}
```

then add your custom fields using the maker command:

```bash
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. TinyGnome):
 > MyEntity
```

Form
----

Create a form for your entity the usual way:

```php
<?php

namespace App\Form;

use App\Entity\MyEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // add your form fields...
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyEntity::class,
        ]);
    }
}
```

Provider
--------

Extend `OHMedia\SecurityBundle\Provider\AbstractEntityProvider`:

```php
<?php

namespace App\Provider;

use App\Entity\MyEntity;
use OHMedia\SecurityBundle\Provider\AbstractEntityProvider;

class MyEntityProvider extends AbstractEntityProvider
{
    public static function getHumanReadable(): string
    {
        // a word/phrase to describe your entity
        // in various flash messages
        return 'my entity';
    }
    
    public function getEntityClass(): string
    {
        return MyEntity::class;
    }
    
    public function getCustomActions(): array
    {
        $actions = ['my-custom-action'];
        
        return $actions;
    }
}
```

Controller & Routing
--------------------

Extend `OHMedia\SecurityBundle\Controller\EntityController`:

```php
<?php

namespace App\Controller;

use App\Form\MyEntityType;
use App\Provider\MyEntityProvider;
use OHMedia\SecurityBundle\Controller\EntityController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class MyEntityController extends EntityController
{
    public function __construct(MyEntityProvider $provider)
    {
        $this->setProvider($provider);
    }
    
    // accessed when you go to /myentity/{id}/my-entity-action
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
        
        // Symfony\Bundle\FrameworkBundle\Controller\Controller::render()
        return $this->render(...);
    }
    
    protected function getActionRoute()
    {
        return 'my_entity_action';
    }
    
    protected function getEntityFormClass()
    {
        return MyEntityType::class;
    }
    
    protected function redirectDeleteAction()
    {
        // redirect to list page
    }
    
    protected function redirectUnlockAction()
    {
        return $this->redirectToAction('update');
    }
    
    protected function redirectCancelAction()
    {
        if ($this->entity->getId()) {
            return $this->redirectToAction('read');
        }
        else {
            // redirect to list page
        }
    }
    
    protected function redirectSaveAction()
    {
        return $this->redirectToAction('read');
    }
    
    protected function renderSaveAction(FormView $formView)
    {
        // Symfony\Bundle\FrameworkBundle\Controller\Controller::render()
        return $this->render(...);
    }

    protected function renderDeleteAction(FormView $formView)
    {
        // Symfony\Bundle\FrameworkBundle\Controller\Controller::render()
        return $this->render(...);
    }
}
```

Create some basic routes:

```yaml
# config/routes/my_entity.yml

my_entity_create:
    path: /my-entity/create
    controller: App\Controller\MyEntityController::createAction

# will handle several actions: 'read', 'update', 'delete', etc.
my_entity_action:
    path: /my-entity/{id}/{action}
    controller: App\Controller\MyEntityController::actionAction
    defaults: { action: read }
    requirements:
        id: \d+
```

The name of your generic action should match what's returned by
`App\Controller\MyEntityController::getActionRoute()`.

Voter
-----

Extend `OHMedia\SecurityBundle\Security\Voter\EntityVoter`:

```php
<?php

namespace App\Security\Voter;

use App\Entity\MyEntity;
use App\Entity\User;
use App\Provider\MyEntityProvider;
use OHMedia\SecurityBundle\Security\Voter\EntityVoter;

class MyEntityVoter extends EntityVoter
{
    public function __construct(MyEntityProvider $provider)
    {
        $this->setProvider($provider);
    }
    
    protected function canCreate(MyEntity $myEntity, User $loggedIn)
    {
        // return true or false
    }
    
    protected function canView(MyEntity $myEntity, User $loggedIn)
    {
        // return true or false
    }
    
    protected function canEdit(MyEntity $myEntity, User $loggedIn)
    {
        // return true or false
    }
    
    protected function canDelete(MyEntity $myEntity, User $loggedIn)
    {
        // return true or false
    }
    
    protected function canMyCustomAction(MyEntity $myEntity, User $loggedIn)
    {
        // return true or false
    }
}
```

Template Helpers
================

Rendering Entity Action Links
-----------------------------

You can use twig helpers for rendering action links on existing entities.
Links are only rendered if the voting passes.

```twig
{{ ohmedia_entity_action(action, entity, route, label, attributes) }}
```

These will only work if the value for 'action' is the same in both
the route AND the voter.

Rendering Entity Create Links
-----------------------------

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
