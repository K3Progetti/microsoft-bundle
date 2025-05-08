<?php

namespace K3Progetti\MicrosoftBundle\Command;


use K3Progetti\MicrosoftBundle\Entity\MicrosoftGroup;
use K3Progetti\MicrosoftBundle\Entity\MicrosoftGroupUser;
use K3Progetti\MicrosoftBundle\Repository\MicrosoftGroupRepository;
use K3Progetti\MicrosoftBundle\Repository\MicrosoftUserRepository;
use K3Progetti\MicrosoftBundle\Service\MicrosoftService;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(name: 'microsoft:microsoft-get-all-groups')]
class GetAllGroupsCommand extends Command
{

    public function __construct(
        private readonly MicrosoftService         $microsoftService,
        private readonly MicrosoftGroupRepository $groupMicrosoftRepository,
        private readonly MicrosoftUserRepository  $microsoftUserRepository

    )
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RandomException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Recupero gli utenti di microsoft');

        $microsoftGroups = $this->microsoftService->getAllGroups();

        $io->progressStart(count($microsoftGroups));
        foreach ($microsoftGroups as $microsoftGroup) {
            $io->progressAdvance();

            // Creo il group
            $microsoftGroupId = $microsoftGroup['id'];
            $displayName = strtolower($microsoftGroup['displayName']);
            $description = $microsoftGroup['description'];


            $groupMicrosoft = $this->groupMicrosoftRepository->findOneBy(['microsoftGroupId' => $microsoftGroupId]);
            if ($groupMicrosoft === null) {
                $groupMicrosoft = new MicrosoftGroup();

            }
            $groupMicrosoft->setMicrosoftGroupId($microsoftGroupId);
            $groupMicrosoft->setName($displayName);
            $groupMicrosoft->setDescription($description);

            $this->groupMicrosoftRepository->save($groupMicrosoft);

            // Recupero le persone per ogni gruppo

            $microsoftUsersByGroup = $this->microsoftService->getUsersByGroupId($microsoftGroupId);
            foreach ($microsoftUsersByGroup as $microsoftUserByGroup) {
                $microsoftUserId = $microsoftUserByGroup['id'];

                $microsoftUser = $this->microsoftUserRepository->findOneBy(['microsoftId' => $microsoftUserId]);

                // Verifico se ho gia l'associazione
                $microsoftGroupUser = $this->microsoftGroupUserRepository->findOneBy(
                    [
                        'group' => $groupMicrosoft->getId(),
                        'user' => $microsoftUser->getId()]
                );

                if ($microsoftGroupUser === null) {
                    $microsoftGroupUser = new MicrosoftGroupUser();
                    $microsoftGroupUser->setGroup($groupMicrosoft->getId());
                    $microsoftGroupUser->setUser($microsoftUser->getId());
                    $this->microsoftUserRepository->save($microsoftGroupUser);
                }


            }


        }
        $io->progressFinish();

        return Command::SUCCESS;
    }


}
