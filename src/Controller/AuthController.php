<?php


namespace K3Progetti\MicrosoftBundle\Controller;


use K3Progetti\MicrosoftBundle\Repository\MicrosoftUserRepository;
use K3Progetti\MicrosoftBundle\Service\MicrosoftService;
use App\Utils\Result;
use Exception;
use K3Progetti\JwtBundle\Helper\AuthHelper;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    private AuthHelper $authHelper;
    private MicrosoftUserRepository $userMicrosoftDataRepository;

    public function __construct(
        Result                  $result,
        MicrosoftService        $microsoftService,
        MicrosoftUserRepository $userMicrosoftDataRepository,
        AuthHelper              $authHelper
    )
    {
        $this->microsoftService = $microsoftService;
        $this->result = $result;
        $this->authHelper = $authHelper;
        $this->userMicrosoftDataRepository = $userMicrosoftDataRepository;
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
    #[Route('/login-microsoft', name: 'login_microsoft', methods: ['POST'])]
    public function loginMicrosoft(Request $request): JsonResponse
    {
        try {


            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $externalToken = $data['accessToken'] ?? null;
            $companyId = $data['companyId'] ?? null;

            if (!$externalToken) {
                return new JsonResponse(['error' => 'Credenziali Microsoft non valide'], Response::HTTP_UNAUTHORIZED);
            }

            $userMicrosoft = $this->microsoftService->getMe($externalToken);

            // Cerco lo User
            $userMicrosoft = $this->userMicrosoftDataRepository->findOneBy(['microsoftId' => $userMicrosoft['id']]);
            if($userMicrosoft === null) {
                throw new RuntimeException('Utente non configurato. Contattare IT');
            }
            $user = $userMicrosoft->getUser();

            // Verifico se l'utente esiste
            $this->authHelper->ensureUserExists($user);

            // Verifico se l'utente è disabilitato
            $this->authHelper->ensureUserIsActive($user, $companyId);

            // Verifico i ruoli
            $this->authHelper->ensureUserRoles($user, $companyId);

            // Genero i token
            $response = $this->authHelper->buildTokenResponse($user, $request);

            return new JsonResponse($response);
        } catch (Exception $ex) {
            $this->result->setMessage($ex->getMessage());
            return new JsonResponse($this->result->toArray(), 422);
        }
    }


}


