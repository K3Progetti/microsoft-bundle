<?php

namespace K3Progetti\MicrosoftBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use K3Progetti\MicrosoftBundle\Contract\UserInterface;
use K3Progetti\MicrosoftBundle\Repository\MicrosoftUserRepository;
use K3Progetti\MicrosoftBundle\Service\MicrosoftService;
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

#[AsCommand(name: 'microsoft:microsoft-get-all-disabled-users')]
class GetAllDisabledUsersCommand extends Command
{
    public function __construct(
        private readonly MicrosoftService        $microsoftService,
        private readonly MicrosoftUserRepository $userMicrosoftDataRepository,
        private readonly EntityManagerInterface  $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Recupero gli utenti che sono stati disattivati microsoft');

        $microsoftUsers = $this->microsoftService->getAllDisabledUsers();
        $io->progressStart(count($microsoftUsers));
        foreach ($microsoftUsers as $microsoftUser) {
            $io->progressAdvance();

            $microsoftId = $microsoftUser['id'];

            $userMicrosoft = $this->userMicrosoftDataRepository->findOneBy(['microsoftId' => $microsoftId]);
            if ($userMicrosoft) {
                /** @var UserInterface $user */
                $user = $userMicrosoft->getUser();
                $user->setActive(false);
                $user->setRoles([]);

                $this->entityManager->flush();
            }
        }
        $io->progressFinish();

        return Command::SUCCESS;
    }
}
