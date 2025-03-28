<?php

namespace K3Progetti\MicrosoftBundle\Command;

use K3Progetti\MicrosoftBridgeBundle\Service\MicrosoftService;
use App\Entity\User;
use App\Repository\UserRepository;
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

#[AsCommand(name: 'microsoft:microsoft-get-all-users')]
class GetAllUsersCommand extends Command
{


    private MicrosoftService $microsoftService;
    private UserRepository $userRepository;

    public function __construct(
        MicrosoftService $microsoftService, UserRepository $userRepository,

    )
    {
        parent::__construct();
        $this->microsoftService = $microsoftService;
        $this->userRepository = $userRepository;
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

        $microsoftUsers = $this->microsoftService->getAllUsers();
        print_r($microsoftUsers);
        $io->progressStart(count($microsoftUsers));
        foreach ($microsoftUsers as $microsoftUser) {
            $io->progressAdvance();

            // Creo lo user
            $externalId = $microsoftUser['id'];
            $email = strtolower($microsoftUser['mail']);
            if (empty($email)) {
                $email = strtolower($microsoftUser['userPrincipalName']);
            }
            $surname = ucwords(strtolower($microsoftUser['surname']));
            $name = ucwords(strtolower($microsoftUser['givenName']));
            $phone = $microsoftUser['mobilePhone'];
            //
            $user = $this->userRepository->findOneBy(['externalId' => $externalId]);
            if ($user === null) {
                // Provo a cercarlo per email
                $user = $this->userRepository->findOneBy(['email' => $email]);
                if ($user === null) {
                    $user = new User();
                    $user->setUsername($email);
                    $user->setActive(false);
                    $user->setPassword(bin2hex(random_bytes(16))); // una password a caso
                }
            }
            if ($user) {
                $user->setExternalId($externalId);
                $user->setEmail($email);
                $user->setSurname($surname);
                $user->setName($name);
                $user->setPhone($phone);
            }

            $this->userRepository->save($user);
        }
        $io->progressFinish();

        return Command::SUCCESS;
    }


}
