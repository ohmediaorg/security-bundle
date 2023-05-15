<?= "<?php\n" ?>

namespace App\Form;

use App\Entity\<?= $singular['pascal_case'] ?>;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class <?= $singular['pascal_case'] ?>Type extends AbstractType
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
            'data_class' => <?= $singular['pascal_case'] ?>::class,
        ]);
    }
}
