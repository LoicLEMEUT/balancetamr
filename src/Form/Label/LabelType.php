<?php

namespace App\Form\Label;

use App\Entity\Label;
use App\Entity\Project;
use App\Entity\Team;
use App\Manager\ProjectManager;
use App\Services\Gitlab\GitlabService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LabelType extends AbstractType
{
    /**
     * @var GitlabService
     */
    private $gitlabService;

    public function __construct(GitlabService $gitlabService)
    {
        $this->gitlabService = $gitlabService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('team',
                EntityType::class,
                [
                    'class' => Team::class,
                    'choice_label' => function (Team $value) {
                        return $value->getName();
                    },
                    'choice_value' => function (Team $value) {
                        return $value->getName();
                    },
                    'disabled' => true,
                    'attr' => [
                        'disabled' => true,
                    ]
                ]
            )
            ->add('project',
                EntityType::class,
                [
                    'class' => Project::class,
                    'choice_loader' => new ProjectByTeamChoiceLoader($builder->getData(), $this->gitlabService),
                    'choice_label' => function (?Project $project) {
                        return $project !== null ? '['.$project->getProvider()->getName().']'. ' - ' .$project->getName() .' ('.$project->getExternalId().')' : '';
                    },
                    'choice_value' => function (Project $project) {
                        return $project->getExternalId();
                    },
                    'disabled' => $options['edit'],
                    'attr' => [
                        'disabled' => $options['edit'],
                    ],
                    'required' => true,

                ]
            );


        // AJAX call for refresh list of contract.
        $formModifier = function (FormInterface $form, $data) {
            $labelsChoices = [];
            $labels = null;

            if ($data instanceof Label && $data->getId() !== null) {
                $labels = $this->gitlabService->getLabelsByProject(
                    $data->getProject()->getProvider(),
                    $data->getProject()->getExternalId()
                );
            }

            if ($data instanceof Project && $data->getExternalId() !== null) {
                $labels = $this->gitlabService->getLabelsByProject(
                    $data->getProvider(),
                    $data->getExternalId()
                );
            }

            if (!empty($labels)) {
                foreach ($labels as $label) {
                    $labelsChoices[$label['name']] = $label['id'];
                }
            }


            $form->add('codes',
                ChoiceType::class,
                [
                    'multiple' => true,
                    'expanded' => true,
                    'placeholder' => 'Choose labels',
                    'choices' => $labelsChoices,
                ]
            );
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your data.
                $data = $event->getData();
                $formModifier($event->getForm(), $data);
            }
        );

        $builder->get('project')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $user_id = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $user_id);
            }
        );


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Label::class,
            'edit' => false,
        ]);
    }
}
