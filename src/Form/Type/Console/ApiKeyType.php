<?php
declare(strict_types = 1);
/**
 * /src/Form/Type/Console/ApiKeyType.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Form\Type\Console;

use App\DTO\ApiKey\ApiKey;
use App\Form\DataTransformer\UserGroupTransformer;
use App\Form\Type\FormTypeLabelInterface;
use App\Form\Type\Traits\AddBasicFieldToForm;
use App\Form\Type\Traits\UserGroupChoices;
use App\Resource\UserGroupResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ApiKeyType
 *
 * @package App\Form\Type\Console
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class ApiKeyType extends AbstractType
{
    // Traits
    use AddBasicFieldToForm;
    use UserGroupChoices;

    /**
     * Base form fields
     *
     * @var array<int, array<int, mixed>>
     */
    private static $formFields = [
        [
            'description',
            Type\TextType::class,
            [
                FormTypeLabelInterface::LABEL => 'Description',
                FormTypeLabelInterface::REQUIRED => true,
                FormTypeLabelInterface::EMPTY_DATA => '',
            ],
        ],
    ];

    /**
     * @var UserGroupTransformer
     */
    private $userGroupTransformer;

    /**
     * ApiKeyType constructor.
     *
     * @param UserGroupResource    $userGroupResource
     * @param UserGroupTransformer $userGroupTransformer
     */
    public function __construct(UserGroupResource $userGroupResource, UserGroupTransformer $userGroupTransformer)
    {
        $this->userGroupResource = $userGroupResource;
        $this->userGroupTransformer = $userGroupTransformer;
    }

    /**
     * @SuppressWarnings("unused")
     *
     * @param FormBuilderInterface $builder
     * @param mixed[]              $options
     *
     * @throws InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $this->addBasicFieldToForm($builder, self::$formFields);

        $builder
            ->add(
                'userGroups',
                Type\ChoiceType::class,
                [
                    'choices' => $this->getUserGroupChoices(),
                    'multiple' => true,
                    'required' => true,
                    'empty_data' => '',
                ]
            );

        $builder->get('userGroups')->addModelTransformer($this->userGroupTransformer);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ApiKey::class,
        ]);
    }
}
