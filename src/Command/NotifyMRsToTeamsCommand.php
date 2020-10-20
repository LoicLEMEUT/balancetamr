<?php

namespace App\Command;

use App\Manager\TeamManager;
use App\Notification\SlackNotificationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class NotifyMRsToTeamsCommand extends Command
{

    protected static $defaultName = 'notify:mrs-to-teams';
    /**
     * @var TeamManager
     */
    private $teamManager;
    /**
     * @var SlackNotificationService
     */
    private $slackNotificationService;

    /**
     * NotifyMRsToTeamsCommand constructor.
     */
    public function __construct(TeamManager $teamManager, SlackNotificationService $slackNotificationService)
    {
        parent::__construct();
        $this->teamManager = $teamManager;
        $this->slackNotificationService = $slackNotificationService;
    }


    protected function configure()
    {
        $this
            ->setName('notify:mrs-to-teams')
            ->setDescription('notify:mrs-to-teams');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Hubside Template
        $hubsideStyle = new OutputFormatterStyle('magenta', 'black', array('bold', 'blink'));
        $output->getFormatter()->setStyle('hubside', $hubsideStyle);

        $output->writeln("\n" . '<hubside>------------------------------------</hubside>');
        $output->writeln('<hubside>--- MR en attente ----</hubside>');
        $output->writeln('<hubside>------------------------------------</hubside>');


        foreach ($this->teamManager->getAllTeams() as $team) {
            if (!empty($team->getSlackNotificationPath())) {
                $slackMrs = [];
                $projectsGitlab = $this->teamManager->getProjectsByTeam($team);

                $output->writeln("\n" . '<hubside>-------- TEAM ' . $team->getName() . '------</hubside>');

                foreach ($this->teamManager->getMrsByTeam($team) as $mr) {
                    $labels = '';
                    if (!empty($mr['labels'])) {
                        $labels = '(';
                        foreach ($mr['labels'] as $label) {
                            $labels .= '`' . $label['name'] . '`,';
                        }
                        $labels .= ')';
                        $labels = str_replace(',)', ')', $labels);
                    }

                    $slackMrs[] = [
                        'text' => sprintf(
                            '(*%s* 👍 | *%s* 👎 | *%s* 💬) [%s] <%s|%s> (<@%s>) %s',
                            $mr['upvotes'],
                            $mr['downvotes'],
                            $mr['user_notes_count'],
                            $projectsGitlab[$mr['project_id']]['name'],
                            $mr['web_url'],
                            substr($mr['title'], 0, 10) . '...',
                            $mr['author']['username'],
                            $labels
                        ),
                        'color' => ($mr['merge_status'] == 'can_be_merged' ? 'green' : 'red')
                    ];

                    $output->writeln("<hubside> [" . $projectsGitlab[$mr['project_id']]['name'] . "] - " . $mr['title'] . " </hubside>");
                }
                $this->slackNotificationService->sendMessageLoginAsInfo($team->getSlackNotificationPath(), 'Liste des MR de l équipe ' . $team->getName(), $slackMrs);

                $output->writeln('<hubside>------------------------------------</hubside>' . "\n");
            }

        }

        return Command::SUCCESS;
    }
}
