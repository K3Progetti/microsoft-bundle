<?php

namespace K3Progetti\MicrosoftBundle\Command;

use K3Progetti\MicrosoftBundle\Repository\UserMicrosoftDataRepository;
use K3Progetti\MicrosoftBundle\Service\MicrosoftService;
use App\Repository\UserRepository;
use App\Utils\Queue\QueuedCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(name: 'microsoft:microsoft-get-all-disabled-users')]
class GetAllDisabledUsersCommand extends Command
{

    private MessageBusInterface $messageBus;

    public function __construct(
        private readonly MicrosoftService            $microsoftService,
        private readonly UserRepository              $userRepository,
        private readonly UserMicrosoftDataRepository $userMicrosoftDataRepository,
        MessageBusInterface                          $messageBus

    )
    {
        parent::__construct();
        $this->messageBus = $messageBus;
    }

    /**
     * @return void
     */
    protected
    function configure(): void
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
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Recupero gli utenti che sono stati disattivati microsoft');

        $microsoftUsers = $this->microsoftService->getAllDisabledUsers();
        print_r($microsoftUsers);
        $io->progressStart(count($microsoftUsers));
        foreach ($microsoftUsers as $microsoftUser) {
            $io->progressAdvance();

            // Creo lo user
            $microsoftId = $microsoftUser['id'];

            //
            $userMicrosoft = $this->userMicrosoftDataRepository->findOneBy(['microsoftId' => $microsoftId]);
            if ($userMicrosoft) {
                $user = $userMicrosoft->getUser();
                $user->setActive(false);
                $user->setRoles([]);

                // Richiamo il command per eliminare i jwtToken
                $this->messageBus->dispatch(new QueuedCommand(
                    'jwt:remove-jwt-token-user',
                    [$user->getId()]
                ));


                $this->userRepository->save($user);
            }
        }
        $io->progressFinish();

        return Command::SUCCESS;
    }


}
