<?php

namespace K3Progetti\MicrosoftBundle\Service;

use Exception;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MicrosoftService
{

    private string $clientId;
    private string $tenantId;
    private string $clientSecret;
    private HttpClientInterface $httpClient;
    private string $graphApiUrl;

    // Override dinamici opzionali
    private ?string $overrideClientId = null;
    private ?string $overrideTenantId = null;
    private ?string $overrideClientSecret = null;

    public function __construct
    (
        HttpClientInterface   $httpClient,
        ParameterBagInterface $params
    )
    {
        $this->clientId = $params->get('microsoft.client_id');
        $this->tenantId = $params->get('microsoft.tenant_id');
        $this->clientSecret = $params->get('microsoft.client_secret');
        $this->graphApiUrl = $params->get('microsoft.graph_api_url');

        $this->httpClient = $httpClient;

    }


    /**
     * Lo utilizzo qualora serva passare i valori dinamicamente
     * @param string $clientId
     * @param string $tenantId
     * @param string $clientSecret
     * @return $this
     */
    public function withCredentials(string $clientId, string $tenantId, string $clientSecret): self
    {
        $this->overrideClientId = $clientId;
        $this->overrideTenantId = $tenantId;
        $this->overrideClientSecret = $clientSecret;
        return $this;
    }

    /**
     * Ottengo l\'access token APP
     *
     * @return string|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getAccessToken(): ?string
    {

        $clientId = $this->overrideClientId ?? $this->clientId;
        $tenantId = $this->overrideTenantId ?? $this->tenantId;
        $clientSecret = $this->overrideClientSecret ?? $this->clientSecret;

        $url = sprintf('https://login.microsoftonline.com/%s/oauth2/v2.0/token', $tenantId);

        $response = $this->httpClient->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ],
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            $data = $response->toArray();
            return $data['access_token'] ?? null;
        } else {
            // Gestisci l'errore di ottenimento del token
            dump("Errore durante l'ottenimento del token di accesso: " . $statusCode);
            dump($response->getContent(false));
            return null;
        }

        $data = $response->toArray();

    }

    /**
     * Restituisce tutti gli utenti
     *
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getAllUsers(): ?array
    {
        $accessToken = $this->getAccessToken();

        try {
            return $this->connect('users', $accessToken)->toArray()['value'];


        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getAllDisabledUsers(): ?array
    {
        $accessToken = $this->getAccessToken();

        try {
            $filters = [
                'accountEnabled' => 'false', // Solo utenti disattivati
            ];

            $selectFields = ['id', 'displayName', 'mail', 'accountEnabled'];

            return $this->connect('users', $accessToken, $selectFields, $filters)->toArray()['value'];


        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @param string $accessToken
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getMe(string $accessToken): ?array
    {
        try {

            return $this->connect('me', $accessToken)->toArray();

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @param string $accessToken
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCompanyMe(string $accessToken): ?array
    {
        try {

            return $this->connect('organization', $accessToken)->toArray();

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @param string $apiName
     * @param string $accessToken
     * @param array|null $selectFields
     * @param array|null $filters
     * @param bool|null $remove
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function connect(
        string $apiName,
        string $accessToken,
        ?array $selectFields = null,
        ?array $filters = null,
        ?bool  $remove = false
    ): ResponseInterface
    {

        $queryString = $this->buildGraphApiQuery($selectFields, $filters);

//        echo $queryString.PHP_EOL.PHP_EOL;
//        die;

        $url = sprintf('%s/%s%s', $this->graphApiUrl, $apiName, $queryString);

        //
        $response = $this->httpClient->request($remove ? 'DELETE' : 'GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ],
        ]);

        // Se la risposta non è 200, restituisco errore
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException($response->getContent(false));

        }

        return $response;
    }

    /**
     * @param array|null $selectFields
     * @param array|null $filters
     * @param int $top
     * @return string
     */
    private function buildGraphApiQuery(?array $selectFields = null, ?array $filters = null, int $top = 100): string
    {
        $queryParts = [];

        // Costruisco la parte dei filtri
        if (!empty($filters)) {
            $filterStrings = [];
            foreach ($filters as $field => $value) {
                $filterStrings[] = "$field eq " . (is_numeric($value) || $value === 'false' || $value === 'true' ? $value : "'$value'");
            }
            $queryParts[] = '$filter=' . implode(' and ', $filterStrings);
        }

        // Selezione di campi specifici
        if (!empty($selectFields)) {
            $queryParts[] = '$select=' . implode(',', $selectFields);
        }

        // Limite di risultati
        if ($top !== null) {
            $queryParts[] = '$top=' . $top;
        }

        return '?' . implode('&', $queryParts);
    }

    /**
     * Restituisce gli utenti di quel gruppo
     *
     * @param string $groupId
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getUsersByGroupId(string $groupId): ?array
    {
        $accessToken = $this->getAccessToken();

        try {
            $apiName = sprintf('groups/%s/members', $groupId);


            return $this->connect($apiName, $accessToken)->toArray()['value'];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Restituisce tutti i gruppi presenti nel tenant
     *
     * @param bool|null $security
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getAllGroups(?bool $security = null): ?array
    {
        $accessToken = $this->getAccessToken();

        try {

            if ($security !== null) {
                $filters = [
                    'securityEnabled' => $security ? 'true' : 'false', // Solo i gruppi sicurezza
                ];
            }


            return $this->connect('groups', $accessToken, null, $filters)->toArray()['value'];


        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @param string $userEmail
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getMessages(string $userEmail): ?array
    {
        $accessToken = $this->getAccessToken();

        try {
            $apiName = sprintf('users/%s/messages', $userEmail);
            $response = $this->connect($apiName, $accessToken);
            return $response->toArray()['value'];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @param string $userEmail
     * @param string $messageId
     * @return bool
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function deleteEmail(string $userEmail, string $messageId): bool
    {
        $accessToken = $this->getAccessToken();

        try {
            $apiName = sprintf('users/%s/messages/%s', $userEmail, $messageId);

            $response = $this->connect($apiName, $accessToken, null, null, true);

            return $response->getStatusCode() === 204; // 204 No Content = eliminazione avvenuta

        } catch (Exception $e) {
            dump("Errore durante l'eliminazione dell'email: " . $e->getMessage());
            return false;
        }
    }


}

