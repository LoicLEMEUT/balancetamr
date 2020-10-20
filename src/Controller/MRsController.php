<?php

namespace App\Controller;

use App\Entity\Team;
use App\Manager\TeamManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mrs")
 */
class MRsController extends AbstractController
{
    /**
     * @var TeamManager
     */
    private $teamManager;

    public function __construct(TeamManager $teamManager)
    {
        $this->teamManager = $teamManager;
    }

    /**
     * @Route("/{id}", name="mrs_list_by_team")
     * @param Team $team
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listMrByTeam(Team $team)
    {
        return $this->render('mrs/index.html.twig', [
            'controller_name' => 'HomeController',
            'team' => $team,
            'mrs' => $this->teamManager->getMrsByTeam($team),
            'projects' => $this->teamManager->getProjectsByTeam($team),
        ]);
    }
}
