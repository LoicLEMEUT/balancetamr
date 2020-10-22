<?php


namespace App\Twig\Extension;

use App\Entity\Label;
use App\Entity\Project;
use App\Services\Gitlab\GitlabService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GitlabExtension extends AbstractExtension
{
    /**
     * @var GitlabService
     */
    private $gitlabService;

    public function __construct(GitlabService $gitlabService)
    {
        $this->gitlabService = $gitlabService;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('formatToGitlabProject', [$this, 'formatToGitlabProject']),
            new TwigFunction('formatToGitlabLabel', [$this, 'formatToGitlabLabel']),
        ];
    }

    public function formatToGitlabProject(Project $project)
    {
        $gitlabProject = $this->gitlabService->getProjectById($project);
        return '[' . $project->getProvider()->getName() . '] ' . $gitlabProject['name'] . ' (' . $gitlabProject['id'] . ')';
    }

    public function formatToGitlabLabel(Label $label)
    {
        $labels = [];
        foreach ($label->getCodes() as $id){
            $labels[] = $this->gitlabService->getLabelByProject($label->getProject(), $id);
        }
        return $labels;
    }
}
