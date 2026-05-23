<?php
declare(strict_types=1);

return [
    'active_nav' => 'architecture',
    'title' => 'CaiLama - Architektur',
    'canonical_path' => '/architecture.php',
    'meta_description' => 'Architektur, Datenflüsse und Schnittstellen des CaiLama-Ökosystems.',
    'footer_label' => 'CaiLama-Architektur',
    'footer_links' => [
        ['label' => 'Roadmap', 'href' => 'roadmap.php'],
    ],
    'hero' => [
        'eyebrow' => 'Architektur',
        'headline' => 'CaiLama bleibt Kern. Router und Search bleiben Dienste.',
        'lead' => 'Die Architektur folgt einer einfachen Grenze: Schachproduktlogik gehört in CaiLama. Modellzugriff gehört in den Router. Suche, DWZ und RAG-Kontext gehören in CaiLama-Search. Der Master dokumentiert und prüft, führt aber keine Runtime-Logik der Unterprojekte aus.',
    ],
    'contracts' => [
        ['CaiLama → LLM-Router', '/v1/chat/completions, /v1/models, /health', 'Rollen-Aliase, Fallbacks, Usage-Metriken und keine Schachproduktlogik im Router.'],
        ['CaiLama → Search', 'POST /v1/search, POST /v1/context, /v1/dwz/search, /v1/dwz/player/{pkz}', 'Normalisierte Items/Results und Context/Sources; Browser-Websuche bleibt expliziter Fallback.'],
        ['CaiLama → Webspace-DB-API', 'POST /api/v1/status, Imports, Admin-Schema', 'Geschützte POST-Endpunkte mit Bearer-Key, keine generische SQL-over-HTTP-API.'],
        ['Master → alle', 'Doku, Roadmap, Website, Checks, Benchmark-Ablage', 'Keine Runtime-Kopplung, keine Submodules, keine Unterrepo-Dateien im Master.'],
    ],
    'principles' => [
        ['title' => 'Keine Parallelstrukturen', 'text' => 'Vorhandene Module werden wiederverwendet; neue Fachlogik entsteht zuerst importierbar und testbar.'],
        ['title' => 'HTTP/API-Kopplung', 'text' => 'Dienste werden über klare Endpunkte verbunden, nicht hart ineinander verwoben.'],
        ['title' => 'Konfiguration getrennt', 'text' => 'Code, Doku und Beispiele enthalten keine lokalen Credentials oder produktiven Secrets.'],
        ['title' => 'Messbare Qualität', 'text' => 'Analyse-, Search-, Router- und OCR/FEN-Pfade brauchen Benchmark-Artefakte statt nur subjektiver Einschätzung.'],
    ],
];
