<?php echo "<?php\n"; ?>

namespace App\Controller;

use App\Entity\<?php echo $singular['pascal_case']; ?>;
use App\Form\<?php echo $singular['pascal_case']; ?>Type;
use App\Repository\<?php echo $singular['pascal_case']; ?>Repository;
use App\Security\Voter\<?php echo $singular['pascal_case']; ?>Voter;
use OHMedia\SecurityBundle\Form\DeleteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class <?php echo $singular['pascal_case']; ?>Controller extends AbstractController
{
    #[Route('/<?php echo $plural['kebab_case']; ?>', name: '<?php echo $singular['snake_case']; ?>_index', methods: ['GET'])]
    public function index(<?php echo $singular['pascal_case']; ?>Repository $<?php echo $singular['camel_case']; ?>Repository): Response
    {
        $new<?php echo $singular['pascal_case']; ?> = new <?php echo $singular['pascal_case']; ?>();

        $this->denyAccessUnlessGranted(
            <?php echo $singular['pascal_case']; ?>Voter::INDEX,
            $new<?php echo $singular['pascal_case']; ?>,
            'You cannot access the list of <?php echo $plural['readable']; ?>.'
        );

        $<?php echo $plural['camel_case']; ?> = $<?php echo $singular['camel_case']; ?>Repository->findAll();

        return $this->render('<?php echo $singular['snake_case']; ?>/<?php echo $singular['snake_case']; ?>_index.html.twig', [
            '<?php echo $plural['snake_case']; ?>' => $<?php echo $plural['camel_case']; ?>,
            'new_<?php echo $singular['snake_case']; ?>' => $new<?php echo $singular['pascal_case']; ?>,
            'attributes' => [
                'create' => <?php echo $singular['pascal_case']; ?>Voter::CREATE,
                'delete' => <?php echo $singular['pascal_case']; ?>Voter::DELETE,
                'edit' => <?php echo $singular['pascal_case']; ?>Voter::EDIT,
<?php if ($has_view_route) { ?>
                'view' => <?php echo $singular['pascal_case']; ?>Voter::VIEW,
<?php } ?>
            ],
        ]);
    }

    #[Route('/<?php echo $singular['kebab_case']; ?>/create', name: '<?php echo $singular['snake_case']; ?>_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        <?php echo $singular['pascal_case']; ?>Repository $<?php echo $singular['camel_case']; ?>Repository
    ): Response {
        $<?php echo $singular['camel_case']; ?> = new <?php echo $singular['pascal_case']; ?>();

        $this->denyAccessUnlessGranted(
            <?php echo $singular['pascal_case']; ?>Voter::CREATE,
            $<?php echo $singular['camel_case']; ?>,
            'You cannot create a new <?php echo $singular['readable']; ?>.'
        );

        $form = $this->createForm(<?php echo $singular['pascal_case']; ?>Type::class, $<?php echo $singular['camel_case']; ?>);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $<?php echo $singular['camel_case']; ?>Repository->save($<?php echo $singular['camel_case']; ?>, true);

            $this->addFlash('notice', 'The <?php echo $singular['readable']; ?> was created successfully.');

<?php if ($has_view_route) { ?>
            return $this->redirectToRoute('<?php echo $singular['snake_case']; ?>_view', [
                'id' => $<?php echo $singular['camel_case']; ?>->getId(),
            ]);
<?php } else { ?>
            return $this->redirectToRoute('<?php echo $singular['snake_case']; ?>_index');
<?php } ?>
        }

        return $this->render('<?php echo $singular['snake_case']; ?>/<?php echo $singular['snake_case']; ?>_create.html.twig', [
            'form' => $form->createView(),
            '<?php echo $singular['snake_case']; ?>' => $<?php echo $singular['camel_case']; ?>,
        ]);
    }
<?php if ($has_view_route) { ?>

    #[Route('/<?php echo $singular['kebab_case']; ?>/{id}', name: '<?php echo $singular['snake_case']; ?>_view', methods: ['GET'])]
    public function view(<?php echo $singular['pascal_case']; ?> $<?php echo $singular['camel_case']; ?>): Response
    {
        $this->denyAccessUnlessGranted(
            <?php echo $singular['pascal_case']; ?>Voter::VIEW,
            $<?php echo $singular['camel_case']; ?>,
            'You cannot view this <?php echo $singular['readable']; ?>.'
        );

        return $this->render('<?php echo $singular['snake_case']; ?>/<?php echo $singular['snake_case']; ?>_view.html.twig', [
            '<?php echo $singular['snake_case']; ?>' => $<?php echo $singular['camel_case']; ?>,
        ]);
    }
<?php } ?>

    #[Route('/<?php echo $singular['kebab_case']; ?>/{id}/edit', name: '<?php echo $singular['snake_case']; ?>_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        <?php echo $singular['pascal_case']; ?> $<?php echo $singular['camel_case']; ?>,
        <?php echo $singular['pascal_case']; ?>Repository $<?php echo $singular['camel_case']; ?>Repository
    ): Response {
        $this->denyAccessUnlessGranted(
            <?php echo $singular['pascal_case']; ?>Voter::EDIT,
            $<?php echo $singular['camel_case']; ?>,
            'You cannot edit this <?php echo $singular['readable']; ?>.'
        );

        $form = $this->createForm(<?php echo $singular['pascal_case']; ?>Type::class, $<?php echo $singular['camel_case']; ?>);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $<?php echo $singular['camel_case']; ?>Repository->save($<?php echo $singular['camel_case']; ?>, true);

            $this->addFlash('notice', 'The <?php echo $singular['readable']; ?> was updated successfully.');

<?php if ($has_view_route) { ?>
            return $this->redirectToRoute('<?php echo $singular['snake_case']; ?>_view', [
                'id' => $<?php echo $singular['camel_case']; ?>->getId(),
            ]);
<?php } else { ?>
            return $this->redirectToRoute('<?php echo $singular['snake_case']; ?>_index');
<?php } ?>
        }

        return $this->render('<?php echo $singular['snake_case']; ?>/<?php echo $singular['snake_case']; ?>_edit.html.twig', [
            'form' => $form->createView(),
            '<?php echo $singular['snake_case']; ?>' => $<?php echo $singular['camel_case']; ?>,
        ]);
    }

    #[Route('/<?php echo $singular['kebab_case']; ?>/{id}/delete', name: '<?php echo $singular['snake_case']; ?>_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        <?php echo $singular['pascal_case']; ?> $<?php echo $singular['camel_case']; ?>,
        <?php echo $singular['pascal_case']; ?>Repository $<?php echo $singular['camel_case']; ?>Repository
    ): Response {
        $this->denyAccessUnlessGranted(
            <?php echo $singular['pascal_case']; ?>Voter::DELETE,
            $<?php echo $singular['camel_case']; ?>,
            'You cannot delete this <?php echo $singular['readable']; ?>.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $<?php echo $singular['camel_case']; ?>Repository->remove($<?php echo $singular['camel_case']; ?>, true);

            $this->addFlash('notice', 'The <?php echo $singular['readable']; ?> was deleted successfully.');

            return $this->redirectToRoute('<?php echo $singular['snake_case']; ?>_index');
        }

        return $this->render('<?php echo $singular['snake_case']; ?>/<?php echo $singular['snake_case']; ?>_delete.html.twig', [
            'form' => $form->createView(),
            '<?php echo $singular['snake_case']; ?>' => $<?php echo $singular['camel_case']; ?>,
        ]);
    }
}
