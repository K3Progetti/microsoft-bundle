<?php


namespace K3Progetti\MicrosoftBundle\Controller;


use K3Progetti\MicrosoftBundle\Service\MicrosoftService;
use App\Repository\UserRepository;
use App\Utils\Result;
use Exception;
use K3Progetti\JwtBundle\Helper\AuthHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


class AuthController extends AbstractController
{
    private MicrosoftService $microsoftService;
    private Result $result;
    private UserRepository $userRepository;
    private AuthHelper $authHelper;

    public function __construct(
        Result           $result,
        MicrosoftService $microsoftService,
        UserRepository   $userRepository,
        AuthHelper       $authHelper
    )
    {
        $this->microsoftService = $microsoftService;
        $this->result = $result;
        $this->userRepository = $userRepository;
        $this->authHelper = $authHelper;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/login_microsoft', name: 'login_microsoft', methods: ['POST'])]
    public function loginMicrosoft(Request $request): JsonResponse
    {
        try {


            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $externalToken = $data['accessToken'] ?? null;

            if (!$externalToken) {
                return new JsonResponse(['error' => 'Credenziali Microsoft non valide'], Response::HTTP_UNAUTHORIZED);
            }

            $userMicrosoft = $this->microsoftService->getMe($externalToken);

            // Cerco lo User
            $user = $this->userRepository->findOneBy(['externalId' => $userMicrosoft['id']]);

            // Verifico se l'utente esiste
            $this->authHelper->ensureUserExists($user);

            // Verifico se l'utente è disabilitato
            $this->authHelper->ensureUserIsActive($user);

            // Verifico se ha dei ruoli
            if (count($user->getRoles()) === 0) {
                return new JsonResponse(['message' => 'Account non configurato'], Response::HTTP_LOCKED);
            }

            $response = $this->authHelper->buildTokenResponse($user, $request);


            return new JsonResponse($response);
        } catch (Exception $ex) {
            $this->result->setMessage($ex->getMessage());
            return new JsonResponse($this->result->toArray(), 422);
        }
    }


}


