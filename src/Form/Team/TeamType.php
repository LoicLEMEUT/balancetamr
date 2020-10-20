<?php

namespace App\Form\Team;

use App\Entity\Project;
use App\Entity\Team;
use App\Manager\ProjectManager;
use App\Services\Gitlab\GitlabService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    /**
     * @var GitlabService
     */
    private $gitlabService;
    /**
     * @var ProjectManager
     */
    private $projectManager;

    public function __construct(GitlabService $gitlabService, ProjectManager $projectManager)
    {
        $this->gitlabService = $gitlabService;
        $this->projectManager = $projectManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('email')
            ->add('avatar')
            ->add('slackNotificationPath', TextType::class)
            ->add(
                'projects',
                EntityType::class,
                [
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true,
                    'class' => Project::class,
                    'choice_loader' => new ProjectTypeChoiceLoader($this->gitlabService, $this->projectManager),
                    'choice_label' => function (Project $value) {
                        return '['.$value->getProvider().']'. ' - ' .$value->getName() .' ('.$value->getExternalId().')';
                    },
                    'choice_value' => function (Project $value) {
                        return $value->getExternalId();
                    },
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
