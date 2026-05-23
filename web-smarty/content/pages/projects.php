<?php
declare(strict_types=1);

return [
    'active_nav' => 'projects',
    'title' => 'CaiLama - Projekte',
    'canonical_path' => '/projects.php',
    'meta_description' => 'Detailübersicht der CaiLama-Repositories und ihrer aktuellen Aufgaben.',
    'footer_label' => 'CaiLama-Projekte',
    'footer_links' => [
        ['label' => 'Start', 'href' => 'index.php'],
    ],
    'hero' => [
        'eyebrow' => 'Repository-Stand',
        'headline' => 'Getrennte Repos, gemeinsame Richtung.',
        'lead' => 'CaiLama ist bewusst nicht als Monorepo gebaut. Das Hauptsystem entwickelt die Schach- und Trainingslogik. Der Router kapselt Modellzugriffe. Search liefert Quellen, DWZ und RAG-Kontext. Der Master dokumentiert, prüft und veröffentlicht den Stand auf cailama.org.',
    ],
    'projects' => [
        [
            'id' => 'cailama',
            'name' => 'CaiLama',
            'tag' => 'Hauptsystem',
            'repo_url' => 'https://github.com/TotoBa/CaiLama',
            'summary' => 'CaiLama ist der Produktkern mit Partieimport, PGN-Verarbeitung, Brettwahrheit, Stockfish-Analyse, Trainingssessions, Spielerprofilen, Agent-CLI, OCR/Knowledge und DGT-nahen Workflows.',
            'status' => 'Der personalisierte Trainingsgenerator erzeugt source.pgn, annotated.pgn, training.json, quality_gates.json, Trainingskarten, Kartentypen, Muster und erklärbare Priorität. Gewichtete Trainingspositionen, Coach-Sitzung, Review-Gate-Console, Planmodus, Plan-Kaskade, Hintergrund-Agenten, Benchmark-Events und Legal-Move-Tags sind vorhanden.',
            'open' => 'Nächster Fokus: bewusste PTG-Live-Verifikation, Legal-Move-Details in Review-/Coach-/Benchmark-Artefakte einhängen, RAG-Provenienz überall sichtbar halten und erweiterte OCR/FEN-Qualitätsgates.',
        ],
        [
            'id' => 'router',
            'name' => 'CaiLama-LLM-Router',
            'tag' => 'LLM-Infrastruktur',
            'tag_class' => 'copper',
            'repo_url' => 'https://github.com/TotoBa/CaiLama-LLM-Router',
            'summary' => 'Der Router ist die LLM-Zugriffsschicht. Clients fragen Rollen oder Aliase an; der Router entscheidet über Backend, Modell, Fallback und Diagnose.',
            'status' => 'OpenAI-kompatible Endpunkte, Streaming, Backend-Fallbacks, API-Key-Weitergabe, Usage-Metriken, Benchmark-Export, Config-Hot-Reload und Prometheus-/JSON-Metriken sind umgesetzt.',
            'open' => 'Aktuell keine neue direkte Router-Arbeit ohne Live-Smoke, neuen Benchmark-Bedarf oder neues Backend-/Alias-Profil.',
        ],
        [
            'id' => 'search',
            'name' => 'CaiLama-Search',
            'tag' => 'Search / DWZ / RAG',
            'tag_class' => 'red',
            'repo_url' => 'https://github.com/TotoBa/CaiLama-Search',
            'summary' => 'CaiLama-Search ist der schachspezifische Search-, DWZ- und RAG-Dienst. Er stellt kontrollierten Kontext bereit, ohne CaiLama direkt an Meilisearch oder externe Websuche zu koppeln.',
            'status' => 'Search-API, Context-API, DWZ-Pfade, Quellenverwaltung, Jobs, Goldsets, Observability, RAG-Provenienz, Datenvertrag, Benchmark-Export und optionale semantische Suche sind umgesetzt oder vorbereitet. Filter+Hybrid und Multi-Index-Response sind behoben; lexical und hybrid erreichen im Goldset beide Pass-Rate 1.0.',
            'open' => 'Offen bleibt die produktive Freigabeentscheidung für Hybrid auf größerem Eval. DWZ-Staging ist offline verifiziert; semantic.enabled bleibt default false.',
        ],
        [
            'id' => 'master',
            'name' => 'CaiLama-Master',
            'tag' => 'Koordination',
            'tag_class' => 'moss',
            'repo_url' => 'https://github.com/TotoBa/CaiLama-Master',
            'summary' => 'Der Master ist Website, Roadmap, Status, Betrieb und Ecosystem-Dokumentation. Er enthält keine Runtime-Logik der Unterprojekte.',
            'status' => 'cailama.org läuft als PHP-Webspace mit öffentlichem Document Root, privatem Smarty-App-Bereich, Login, geschütztem Benchmark-Feedback, LLM-Referenzen, JSON-Referenz, Sitemap, robots.txt, Deployment-Skript und Webspace-DB-API-Fassade.',
            'open' => 'Benchmark-Feedback mit Router-/CaiLama-Metriken verbinden, Unterrepo-Status regelmäßig synchronisieren, Runtime/Webseite deployen und die Inhalte datengetrieben aktuell halten.',
        ],
    ],
];
