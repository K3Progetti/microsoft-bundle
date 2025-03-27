# MicrosoftBundle

Bundle Symfony per l'integrazione con Microsoft Graph API.

## ✅ Funzionalità

- Recupero utenti da Microsoft 365
- Filtraggio utenti disabilitati
- Servizio centralizzato per interagire con Graph API

---

## 🚀 Installazione

```bash
composer require k3progetti/microsoft-bundle
```

---

## ⚙️ Configurazione

Aggiungi il bundle al `config/bundles.php` se non è registrato automaticamente:

```php
return [
    // ...
    App\Bundle\Microsoft\MicrosoftBundle::class => ['all' => true],
];
```

---

## 🧭 Struttura del Progetto

```
MicrosoftBundle/
├── src/
│   ├── MicrosoftBundle.php
│   ├── Command/
│   │   ├── GetAllUsersCommand.php
│   │   └── GetAllDisabledUsersCommand.php
│   ├── Controller/
│   │   └── AuthController.php
│   ├── DependencyInjection/
│   │   ├── MicrosoftConfiguration.php
│   │   └── MicrosoftExtension.php
│   └── Service/
│       └── MicrosoftService.php
```

---

## 🔧 Comandi Console

```bash
php bin/console microsoft:get-all-users
php bin/console microsoft:get-all-disabled-users
```

---

## 🤝 Contributi

Sono aperto a suggerimenti e miglioramenti!