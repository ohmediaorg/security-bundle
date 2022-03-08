# config/routes/__SNAKECASE__.yml

__SNAKECASE___create:
    path: /__KEBABCASE__/create
    controller: App\Controller\__PASCALCASE__Controller::createAction

# will handle several actions: 'read', 'update', 'delete', etc.
__SNAKECASE___action:
    path: /__KEBABCASE__/{id}/{action}
    controller: App\Controller\__PASCALCASE__Controller::actionAction
    defaults: { action: read }
    requirements:
        id: \d+
