<?php

namespace App\Services\Gitlab;

interface GitlabInterface
{

    public function getProjects();

    public function getProjectById(int $idProject);

    public function getMrsByProject(int $idProject, array $labels);

    public function getLabelsByProject(int $idProject);

    public function getLabelByProject(int $idProject, int $idLabel);

}
