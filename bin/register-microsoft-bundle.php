#!/usr/bin/env php
<?php

$projectRoot = getcwd();
$bundlesFile = $projectRoot . '/config/bundles.php';
$bundleClass = 'K3Progetti\\MicrosoftBundle\\MicrosoftBundle::class';
$bundleLine = "    $bundleClass => ['all' => true],";

$configTarget = $projectRoot . '/config/packages/microsoft.yaml';
$configSource = __DIR__ . '/../resources/config/packages/microsoft.yaml.dist';

$routesFile = $projectRoot . '/config/routes.yaml';
$routesBlock = <<<YAML

microsoft_bundle_routes:
  resource: '@MicrosoftBundle/Controller/'
  type: attribute
YAML;

function green($text): string { return "\033[32m$text\033[0m"; }
function yellow($text): string { return "\033[33m$text\033[0m"; }
function red($text): string { return "\033[31m$text\033[0m"; }

echo yellow("🔍 File bundles: $bundlesFile\n");

if (!file_exists($bundlesFile)) {
    echo red("❌ File config/bundles.php non trovato.\n");
    exit(1);
}

$contents = file_get_contents($bundlesFile);
$argv = $_SERVER['argv'];
$remove = in_array('--remove', $argv, true);

if ($remove) {
    if (strpos($contents, $bundleLine) !== false) {
        $contents = str_replace($bundleLine . "\n", '', $contents);
        $contents = str_replace($bundleLine, '', $contents);
        file_put_contents($bundlesFile, $contents);
        echo green("🗑️  MicrosoftBundle rimosso da config/bundles.php\n");
    } else {
        echo yellow("ℹ️  MicrosoftBundle non presente in config/bundles.php\n");
    }

    if (file_exists($configTarget)) {
        unlink($configTarget);
        echo green("🗑️  File microsoft.yaml rimosso da config/packages.\n");
    }

    if (file_exists($routesFile)) {
        $routesContent = file_get_contents($routesFile);
        if (strpos($routesContent, $routesBlock) !== false) {
            $routesContent = str_replace($routesBlock, '', $routesContent);
            file_put_contents($routesFile, trim($routesContent) . "\n");
            echo green("🗑️  Blocco routes MicrosoftBundle rimosso da config/routes.yaml\n");
        } else {
            echo yellow("ℹ️  Il blocco routes MicrosoftBundle non era presente.\n");
        }
    }
} else {
    if (strpos($contents, $bundleClass) === false) {
        $pattern = '/(return\s+\[\n)(.*?)(\n\];)/s';
        if (preg_match($pattern, $contents, $matches)) {
            $before = $matches[1];
            $middle = rtrim($matches[2]);
            $after = $matches[3];

            $newMiddle = $middle . "\n" . $bundleLine;
            $newContents = $before . $newMiddle . $after;
            file_put_contents($bundlesFile, $newContents);
            echo green("✅ MicrosoftBundle aggiunto in fondo a config/bundles.php\n");
        } else {
            echo red("❌ Errore durante l'inserimento in config/bundles.php\n");
        }
    } else {
        echo yellow("ℹ️  MicrosoftBundle è già presente in config/bundles.php\n");
    }

    if (!file_exists($configTarget)) {
        if (file_exists($configSource)) {
            copy($configSource, $configTarget);
            echo green("✅ File microsoft.yaml copiato in config/packages.\n");
        } else {
            echo red("⚠️  File sorgente microsoft.yaml.dist non trovato.\n");
        }
    } else {
        echo yellow("ℹ️  File microsoft.yaml già presente in config/packages.\n");
    }

    if (file_exists($routesFile)) {
        $routesContent = file_get_contents($routesFile);
        if (strpos($routesContent, $routesBlock) === false) {
            file_put_contents($routesFile, trim($routesContent) . "\n" . $routesBlock . "\n");
            echo green("✅ Blocco routes MicrosoftBundle aggiunto in config/routes.yaml\n");
        } else {
            echo yellow("ℹ️  Il blocco routes MicrosoftBundle è già presente in config/routes.yaml\n");
        }
    } else {
        echo red("❌ File config/routes.yaml non trovato.\n");
    }

    // ➕ Aggiungo variabili .env se mancanti
    $envFile = $projectRoot . '/.env';
    $envVars = [
        'MICROSOFT_CLIENT_ID' => '',
        'MICROSOFT_TENANT_ID' => '',
        'MICROSOFT_CLIENT_SECRET' => '',
    ];

    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        $newLines = [];

        foreach ($envVars as $key => $value) {
            if (!preg_match("/^$key=/m", $envContent)) {
                $newLines[] = "$key=$value";
                echo green("➕ Variabile $key aggiunta al file .env\n");
            } else {
                echo yellow("ℹ️  Variabile $key già presente in .env\n");
            }
        }

        if (!empty($newLines)) {
            file_put_contents($envFile, "\n# Start - Microsoft API\n" . implode("\n", $newLines) . "\n", FILE_APPEND);
        }
    } else {
        echo yellow("⚠️  File .env non trovato. Nessuna variabile aggiunta.\n");
    }
}