<?php
declare(strict_types=1);

return [
    'active_nav' => 'operations',
    'title' => 'CaiLama - Betrieb',
    'canonical_path' => '/operations.php',
    'meta_description' => 'Betrieb, Website-Deployment und Qualitätsregeln des CaiLama-Master-Repositories.',
    'footer_label' => 'CaiLama-Betrieb',
    'footer_links' => [
        ['label' => 'Start', 'href' => 'index.php'],
    ],
    'hero' => [
        'eyebrow' => 'Master-Betrieb',
        'headline' => 'Public Root, private Templates, keine Secrets.',
        'lead' => 'Der Master ist die lesbare Doku-Schicht. Öffentliche Dateien liegen unter web/ und werden nach public/ deployt. CaiLama-eigene Smarty-Templates liegen in web-smarty/ und werden privat als smarty/ neben dem Document Root bereitgestellt.',
    ],
    'rules' => [
        ['title' => 'Keine Unter-Repos', 'text' => 'CaiLama, CaiLama-LLM-Router und CaiLama-Search bleiben ignoriert und werden nicht als Submodules angelegt.'],
        ['title' => 'Keine Secrets', 'text' => 'Keine .env, Tokens, API-Keys, Zertifikate, Passwörter oder lokalen Credential-Werte im Master.'],
        ['title' => 'Keine Runtime', 'text' => 'Der Master koordiniert, dokumentiert und prüft. Produktive Logik gehört in die Ziel-Repos.'],
        ['title' => 'Private Smarty-App', 'text' => 'Smarty-Templates, Content-Daten, Cache und vendor liegen nicht öffentlich unter web/.'],
        ['title' => 'DB-Import', 'text' => 'Große Dumps werden per SFTP in einen nicht öffentlichen Webspace-Ordner gelegt und fachlich begrenzt verarbeitet.'],
        ['title' => 'API-Konfig', 'text' => 'DB-Zugänge und Token-Hashes liegen außerhalb des Public-Webroots.'],
    ],
    'docs' => [
        ['title' => 'docs/roadmap.md', 'text' => 'Roadmap mit Ziel-Repos und Koordinationspunkten.'],
        ['title' => 'docs/integrations.md', 'text' => 'Schnittstellen, Rollen, Endpunkte und Smoke-Test-Grenzen.'],
        ['title' => 'docs/product-positioning.md', 'text' => 'Trainingsfokus, Zielgruppe, Nicht-Ziele und Qualitätsgrenzen.'],
        ['title' => 'docs/benchmarks.md', 'text' => 'Master-Rahmen für Search-, Router-, PTG-, OCR/FEN- und Modellrollen-Benchmarks.'],
        ['title' => 'benchmark-feedback.php', 'text' => 'Geschützte Feedback-Erfassung für Laufzeit, Tokenwerte, Qualität, Logikfehler und A/B-Präferenzen.'],
        ['title' => 'docs/website.md', 'text' => 'Webspace-Aufbau, Smarty-Abhängigkeit, Deploypfad und Checks.'],
        ['title' => 'docs/data/ecosystem.json', 'text' => 'Maschinenlesbare Struktur der Repos, Endpunkte, Rollen und Roadmap.'],
    ],
];
