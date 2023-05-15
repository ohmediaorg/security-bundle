<?= "<?php\n" ?>

namespace App\Controller;

use App\Entity\<?= $singular['pascal_case'] ?>;
use App\Form\<?= $singular['pascal_case'] ?>Type;
use App\Repository\<?= $singular['pascal_case'] ?>Repository;
use App\Security\Voter\<?= $singular['pascal_case'] ?>Voter;
use OHMedia\SecurityBundle\Controller\Traits\BootstrapFlashController;
use OHMedia\SecurityBundle\Form\DeleteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class <?= $singular['pascal_case'] ?>Controller extends AbstractController
{
    use BootstrapFlashController;

    #[Route('/<?= $plural['kebab_case'] ?>', name: '<?= $singular['snake_case'] ?>_index', methods: ['GET'])]
    public function index(<?= $singular['pascal_case'] ?>Repository $<?= $singular['camel_case'] ?>Repository): Response
    {
        $<?= $singular['camel_case'] ?> = new <?= $singular['pascal_case'] ?>();

        $this->denyAccessUnlessGranted(
            <?= $singular['pascal_case'] ?>Voter::INDEX,
            $<?= $singular['camel_case'] ?>,
            'You cannot access the list of <?= $plural['readable'] ?>.'
        );

        $<?= $plural['camel_case'] ?> = $<?= $singular['camel_case'] ?>Repository->findAll();

        return $this->render('<?= $singular['camel_case'] ?>/index.html.twig', [
            '<?= $plural['camel_case'] ?>' => $<?= $plural['camel_case'] ?>,
        ]);
    }

    #[Route('/<?= $singular['kebab_case'] ?>/create', name: '<?= $singular['snake_case'] ?>_create', methods: ['GET', 'PUT'])]
    public function create(
        Request $request,
        <?= $singular['pascal_case'] ?>Repository $<?= $singular['camel_case'] ?>Repository
    ): Response
    {
        $<?= $singular['camel_case'] ?> = new <?= $singular['pascal_case'] ?>();

        $this->denyAccessUnlessGranted(
            <?= $singular['pascal_case'] ?>Voter::CREATE,
            $<?= $singular['camel_case'] ?>,
            'You cannot create a new <?= $singular['readable'] ?>.'
        );

        return $this->form($request, $<?= $singular['camel_case'] ?>, $<?= $singular['camel_case'] ?>Repository);
    }

    #[Route('/<?= $singular['kebab_case'] ?>/{id}', name: '<?= $singular['snake_case'] ?>_view', methods: ['GET'])]
    public function view(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>): Response
    {
        $this->denyAccessUnlessGranted(
            <?= $singular['pascal_case'] ?>Voter::VIEW,
            $<?= $singular['camel_case'] ?>,
            'You cannot view this <?= $singular['readable'] ?>.'
        );

        return $this->render('<?= $singular['camel_case'] ?>/view.html.twig', [
            'form' => $form->createView(),
            '<?= $singular['camel_case'] ?>' => $<?= $singular['camel_case'] ?>,
        ]);
    }

    #[Route('/<?= $singular['kebab_case'] ?>/{id}/edit', name: '<?= $singular['snake_case'] ?>_edit', methods: ['GET', 'POST'])]
    public function edit(
        Connection $connection,
        Request $request,
        <?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>,
        <?= $singular['pascal_case'] ?>Repository $<?= $singular['camel_case'] ?>Repository
    ): Response
    {
        $this->denyAccessUnlessGranted(
            <?= $singular['pascal_case'] ?>Voter::EDIT,
            $<?= $singular['camel_case'] ?>,
            'You cannot edit this <?= $singular['readable'] ?>.'
        );

        return $this->form($request, $<?= $singular['camel_case'] ?>, $<?= $singular['camel_case'] ?>Repository);
    }

    #[Route('/<?= $singular['kebab_case'] ?>/{id}/delete', name: '<?= $singular['snake_case'] ?>_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        <?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>,
        <?= $singular['pascal_case'] ?>Repository $<?= $singular['camel_case'] ?>Repository
    ): Response
    {
        $this->denyAccessUnlessGranted(
            <?= $singular['pascal_case'] ?>Voter::DELETE,
            $<?= $singular['camel_case'] ?>,
            'You cannot delete this <?= $singular['readable'] ?>.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $<?= $singular['camel_case'] ?>Repository->remove($<?= $singular['camel_case'] ?>, true);

            $this->addFlashSuccess('The <?= $singular['readable'] ?> was deleted successfully.');

            return $this->redirectToRoute('<?= $singular['snake_case'] ?>_index');
        }

        return $this->render('<?= $singular['camel_case'] ?>/delete.html.twig', [
            'form' => $form->createView(),
            '<?= $singular['camel_case'] ?>' => $<?= $singular['camel_case'] ?>,
        ]);
    }

    private function form(
        Request $request,
        <?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>,
        <?= $singular['pascal_case'] ?>Repository $<?= $singular['camel_case'] ?>Repository
    ): Response
    {
        $form = $this->createForm(<?= $singular['pascal_case'] ?>Type::class, $<?= $singular['camel_case'] ?>);

        if (!$<?= $singular['camel_case'] ?>->getId()) {
            $form->setMethod('PUT');
        }

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $<?= $singular['camel_case'] ?>Repository->save($<?= $singular['camel_case'] ?>, true);

            $this->addFlashSuccess('Changes to the <?= $singular['readable'] ?> were saved successfully.');

            return $this->redirectToRoute('<?= $singular['snake_case'] ?>_view', [
                'id' => $<?= $singular['camel_case'] ?>->getId(),
            ]);
        }

        return $this->render('<?= $singular['camel_case'] ?>/form.html.twig', [
            'form' => $form->createView(),
            '<?= $singular['camel_case'] ?>' => $<?= $singular['camel_case'] ?>,
        ]);
    }
}
