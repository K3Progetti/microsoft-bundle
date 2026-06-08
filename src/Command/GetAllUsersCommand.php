<?php

namespace K3Progetti\MicrosoftBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use K3Progetti\MicrosoftBundle\Contract\UserInterface;
use K3Progetti\MicrosoftBundle\Entity\MicrosoftUser;
use K3Progetti\MicrosoftBundle\Repository\MicrosoftUserRepository;
use K3Progetti\MicrosoftBundle\Service\MicrosoftService;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(name: 'microsoft:microsoft-get-all-users')]
class GetAllUsersCommand extends Command
{
    public function __construct(
        private readonly MicrosoftService        $microsoftService,
        private readonly MicrosoftUserRepository $userMicrosoftDataRepository,
        private readonly EntityManagerInterface  $entityManager,
        #[Autowire(param: 'microsoft.user_class')] private readonly string $userClass,
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
     * @throws RandomException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Recupero gli utenti di microsoft');

        $microsoftUsers = $this->microsoftService->getAllUsers();

        $io->progressStart(count($microsoftUsers));
        foreach ($microsoftUsers as $microsoftUser) {
            $io->progressAdvance();

            $microsoftId = $microsoftUser['id'];
            $email = strtolower($microsoftUser['mail']);
            if (empty($email)) {
                $email = strtolower($microsoftUser['userPrincipalName']);
            }
            $surname = ucwords(strtolower($microsoftUser['surname']));
            $name = ucwords(strtolower($microsoftUser['givenName']));
            $phone = $microsoftUser['mobilePhone'];

            $user = null;
            $userMicrosoft = $this->userMicrosoftDataRepository->findOneBy(['microsoftId' => $microsoftId]);
            if ($userMicrosoft === null) {
                $userMicrosoft = new MicrosoftUser();
                $userMicrosoft->setMicrosoftId($microsoftId);
            } else {
                /** @var UserInterface|null $user */
                $user = $this->entityManager->getRepository($this->userClass)->findOneBy(['email' => $email]);
            }

            if ($user === null) {
                /** @var UserInterface $user */
                $user = new $this->userClass();
                $user->setUsername($email);
                $user->setEmail($email);
                $user->setActive(false);
                $user->setPassword(bin2hex(random_bytes(16)));

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $userMicrosoft->setUser($user);
                $this->userMicrosoftDataRepository->save($userMicrosoft);
            }

            if ($user) {
                $user->setSurname($surname);
                $user->setName($name);
                $user->setPhone($phone);

                $this->entityManager->flush();
            }
        }
        $io->progressFinish();

        return Command::SUCCESS;
    }
}
