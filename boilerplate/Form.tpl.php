<?php echo "<?php\n"; ?>

namespace App\Form;

use App\Entity\<?php echo $singular['pascal_case']; ?>;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class <?php echo $singular['pascal_case']; ?>Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // add your form fields...
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => <?php echo $singular['pascal_case']; ?>::class,
        ]);
    }
}
