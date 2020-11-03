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
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * NotifyMRsToTeamsCommand constructor.
     * @param TeamManager $teamManager
     * @param SlackNotificationService $slackNotificationService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TeamManager $teamManager,
        SlackNotificationService $slackNotificationService,
        TranslatorInterface $translator
    )
    {
        parent::__construct();
        $this->teamManager = $teamManager;
        $this->slackNotificationService = $slackNotificationService;
        $this->translator = $translator;
    }


    protected function configure()
    {
        $this
            ->setName('notify:mrs-to-teams')
            ->setDescription('notify:mrs-to-teams');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputStyle = new OutputFormatterStyle('magenta', 'black', array('bold', 'blink'));
        $output->getFormatter()->setStyle('mrnotify', $outputStyle);

        $output->writeln("\n");
        $output->writeln('<mrnotify>------------------------------------</mrnotify>');
        $output->writeln('<mrnotify>--- ' . $this->translator->trans('mr.title') . ' ----</mrnotify>');
        $output->writeln('<mrnotify>------------------------------------</mrnotify>');


        foreach ($this->teamManager->getAllTeams() as $team) {
            if (!empty($team->getSlackNotificationPath())) {
                $slackMrs = [];
                $projectsGitlab = $this->teamManager->getProjectsByTeam($team);

                $output->writeln("\n");
                $output->writeln('<mrnotify>-------- ' . $this->translator->trans('team.title', ['team_name' => $team->getName()]) . '------</mrnotify>');

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

                    $projectName = $projectsGitlab[$mr['balancetamr_provider_id']][$mr['project_id']]['name'];

                    $slackMrs[] = [
                        'text' => sprintf(
                            '(*%s* 👍 | *%s* 👎 | *%s* 💬) [%s] <%s|%s> (<@%s>) %s',
                            $mr['upvotes'],
                            $mr['downvotes'],
                            $mr['user_notes_count'],
                            $projectName,
                            $mr['web_url'],
                            substr($mr['title'], 0, 10) . '...',
                            $mr['author']['username'],
                            $labels
                        ),
                        'color' => ($mr['merge_status'] === 'can_be_merged' ? 'green' : 'red')
                    ];

                    $output->writeln("<mrnotify> [" . $projectName . "] - " . $mr['title'] . " </mrnotify>");
                }

                $this->slackNotificationService->sendMessageLoginAsInfo(
                    $team->getSlackNotificationPath(),
                    $this->translator->trans('mr.slack_title', ['%team_name%' => $team->getName()]),
                    $slackMrs
                );

                $output->writeln('<mrnotify>------------------------------------</mrnotify>');
                $output->writeln("\n");
            }
        }

        return Command::SUCCESS;
    }
}
