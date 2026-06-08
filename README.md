# MicrosoftBundle

Bundle Symfony per l'integrazione con Microsoft Graph API.

## Requisiti

- PHP >= 8.2
- Symfony 8.x
- Doctrine ORM 3.x

---

## Funzionalità

- Recupero utenti da Microsoft 365
- Filtraggio utenti disabilitati
- Recupero e cancellazione email tramite Graph API
- Gestione gruppi e appartenenze
- Login tramite token Microsoft
- Servizio centralizzato per interagire con Graph API

---

## Installazione

```bash
composer require k3progetti/microsoft-bundle
```

---

## Configurazione

Aggiungi il bundle al `config/bundles.php` se non è registrato automaticamente:

```php
return [
    // ...
    K3Progetti\MicrosoftBundle\MicrosoftBundle::class => ['all' => true],
];
```

Aggiungi la configurazione in `config/packages/microsoft.yaml`:

```yaml
microsoft:
    user_class: App\Entity\User          # obbligatorio: la tua entità User
    client_id: '%env(MICROSOFT_CLIENT_ID)%'
    tenant_id: '%env(MICROSOFT_TENANT_ID)%'
    client_secret: '%env(MICROSOFT_CLIENT_SECRET)%'
    graph_api_url: 'https://graph.microsoft.com/v1.0'  # opzionale, questo è il valore di default
    auth:
        allowed_groups: []  # opzionale, lista di group ID permessi
```

Aggiungi le variabili d'ambiente nel tuo `.env`:

```env
MICROSOFT_CLIENT_ID=your-client-id
MICROSOFT_TENANT_ID=your-tenant-id
MICROSOFT_CLIENT_SECRET=your-client-secret
```

---

## Integrazione con la tua entità User

La tua entità `User` deve implementare `K3Progetti\MicrosoftBundle\Contract\UserInterface`:

```php
use K3Progetti\MicrosoftBundle\Contract\UserInterface as MicrosoftUserInterface;

class User implements MicrosoftUserInterface
{
    public function getId(): mixed { ... }
    public function setUsername(string $username): static { ... }
    public function setEmail(string $email): static { ... }
    public function setActive(bool $active): static { ... }
    public function setPassword(string $password): static { ... }
    public function setSurname(?string $surname): static { ... }
    public function setName(?string $name): static { ... }
    public function setPhone(?string $phone): static { ... }
    public function setRoles(array $roles): static { ... }
}
```

Il bundle usa `resolve_target_entities` di Doctrine per collegare l'interfaccia alla tua classe concreta in modo automatico, senza dipendenze hardcoded su `App\*`.

---

## Struttura del Progetto

```
MicrosoftBundle/
├── src/
│   ├── MicrosoftBundle.php
│   ├── Command/
│   │   ├── GetAllUsersCommand.php
│   │   ├── GetAllDisabledUsersCommand.php
│   │   └── GetAllGroupsCommand.php
│   ├── Controller/
│   │   └── AuthController.php
│   ├── DependencyInjection/
│   │   ├── MicrosoftConfiguration.php
│   │   └── MicrosoftExtension.php
│   ├── Entity/
│   │   ├── MicrosoftUser.php
│   │   ├── MicrosoftGroup.php
│   │   └── MicrosoftGroupUser.php
│   ├── Repository/
│   │   ├── MicrosoftUserRepository.php
│   │   ├── MicrosoftGroupRepository.php
│   │   └── MicrosoftGroupUserRepository.php
│   └── Service/
│       └── MicrosoftService.php
```

---

## Comandi Console

```bash
php bin/console microsoft:get-all-users
php bin/console microsoft:get-all-disabled-users
php bin/console microsoft:get-all-groups
```

---

## Utilizzo del Servizio

```php
use K3Progetti\MicrosoftBundle\Service\MicrosoftService;

class MyService
{
    public function __construct(private MicrosoftService $microsoftService) {}

    public function example(): void
    {
        // Recupera tutti gli utenti
        $users = $this->microsoftService->getAllUsers();

        // Recupera i membri di un gruppo
        $members = $this->microsoftService->getUsersByGroupId('group-id');

        // Recupera tutti i gruppi di sicurezza
        $groups = $this->microsoftService->getAllGroups(security: true);

        // Recupera le email di un utente
        $messages = $this->microsoftService->getMessages('user@example.com');

        // Credenziali dinamiche (override della configurazione globale)
        $this->microsoftService
            ->withCredentials($clientId, $tenantId, $clientSecret)
            ->getAllUsers();
    }
}
```

---

## Contributi

Sono aperto a suggerimenti e miglioramenti!
