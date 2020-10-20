<?php

namespace App\Controller;

use App\Entity\Label;
use App\Entity\Team;
use App\Form\Label\LabelType;
use App\Manager\LabelManager;
use App\Repository\LabelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/label")
 */
class LabelController extends AbstractController
{
    /**
     * @var LabelManager
     */
    private $labelManager;

    public function __construct(LabelManager $labelManager)
    {
        $this->labelManager = $labelManager;
    }

    /**
     * @Route("/{id}", name="label_index", methods={"GET"})
     * @param Team $team
     * @return Response
     */
    public function index(Team $team): Response
    {
        return $this->render('label/index.html.twig', [
            'labels' => $this->labelManager->findByTeam($team),
            'team' => $team,
        ]);
    }

    /**
     * @Route("/{id}/new", name="label_new", methods={"GET","POST"})
     * @param Request $request
     * @param Team $team
     * @return Response
     */
    public function new(Request $request, Team $team): Response
    {
        $label = new Label();
        $label->setTeam($team);
        $form = $this->createForm(LabelType::class, $label);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->labelManager->save($label);
            return $this->redirectToRoute('label_index', ['id' => $team->getId()]);
        }

        return $this->render('label/new.html.twig', [
            'label' => $label,
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    /**
     * @Route("/{id}/edit/{idLabel}", name="label_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Team $team
     * @param int $idLabel
     * @return Response
     */
    public function edit(Request $request, Team $team, int $idLabel): Response
    {
        $label = $this->labelManager->findById($idLabel);

        $form = $this->createForm(LabelType::class, $label, ['edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->labelManager->save($label);

            return $this->redirectToRoute('label_index', ['id' => $team->getId()]);
        }

        return $this->render('label/edit.html.twig', [
            'label' => $label,
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    /**
     * @Route("{id}/{idLabel}", name="label_delete", methods={"DELETE"})
     * @param Request $request
     * @param Team $team
     * @param int $idLabel
     * @return Response
     */
    public function delete(Request $request, Team $team, int $idLabel): Response
    {
        $label = $this->labelManager->findById($idLabel);
        if ($label && $this->isCsrfTokenValid('delete'.$label->getId(), $request->request->get('_token'))) {
            $this->labelManager->remove($label);
        }

        return $this->redirectToRoute('label_index', ['id' => $team->getId()]);
    }
}
