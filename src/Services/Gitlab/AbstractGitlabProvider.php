<?php

namespace App\Services\Gitlab;

use Gitlab\Client;

abstract class AbstractGitlabProvider implements GitlabInterface
{
    /** @var Client */
    protected $client;

    public function getProjects()
    {
        $projects = [];

        // Variants.
        $page = 1;
        do {
            $projectsCurrent = $this->client->projects()->all(
                [
                    'archived' => false,
                    'per_page' => 100,
                    'page' => $page,
                ]
            );

            $projects = array_merge($projects, $projectsCurrent);
            $page++;
        } while (!empty($projectsCurrent));

        return $projects;
    }

    public function getProjectById(int $idProject)
    {
        return $this->client->projects()->show($idProject);
    }

    public function getMrsByProject(int $idProject, array $labels)
    {
        $mrs = [];

        $page = 1;
        do {
            $mrsCurrent = $this->client->mergeRequests()->all(
                $idProject,
                [
                    'scope' => 'all',
                    'state' => 'opened',
                    'per_page' => 100,
                    'page' => $page,
                    'wip' => 'no',
                    'with_labels_details' => true,
                ]
            );

            // Filter Labels request in mode OR (Gitlab apply only mode AND).
            if (!empty($labels)) {
                foreach ($mrsCurrent as $key => $mr) {
                    if (!empty($mr['labels'])) {
                        $hasLabel = false;
                        foreach ($mr['labels'] as $mrLabel) {
                            if (in_array($mrLabel['id'], $labels, true)) {
                                $hasLabel = true;
                                break;
                            }
                        }
                        if (!$hasLabel) {
                            unset($mrsCurrent[$key]);
                        }
                    }
                }
            }

            $mrs = array_merge($mrs, $mrsCurrent);
            $page++;
        } while (!empty($mrsCurrent));

        return $mrs;
    }

    public function getLabelsByProject(int $idProject)
    {
        return $this->client->projects()->labels($idProject);
    }

    public function getLabelByProject(int $idProject, int $idLabel)
    {
        $labels = $this->client->projects()->labels($idProject);
        foreach ($labels as $label){
            if($label['id'] === $idLabel){
                return $label;
            }
        }
        return null;
    }

}
