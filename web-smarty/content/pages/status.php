<?php
declare(strict_types=1);

return [
    'active_nav' => 'status',
    'title' => 'CaiLama - Status',
    'canonical_path' => '/status.php',
    'meta_description' => 'Projektstand des CaiLama-Ökosystems mit Hauptsystem, LLM-Router, Search/RAG und Master-Dokumentation.',
    'footer_label' => 'CaiLama-Status',
    'footer_links' => [
        ['label' => 'Master-Repository', 'href' => 'https://github.com/TotoBa/CaiLama-Master'],
    ],
    'og' => [
        'title' => 'CaiLama',
        'description' => 'Modulares Schachtraining mit Analyse, LLM, Search/RAG und personalisierten Trainingspfaden.',
    ],
    'hero' => [
        'eyebrow' => 'Projektstand',
        'headline' => 'Vier Repos, ein Trainingssystem.',
        'lead' => 'CaiLama besteht aus einem produktnahen Schachkern, einem generischen LLM-Router, einem schachspezifischen Search-/DWZ-/RAG-Dienst und einem Master-Repo für Website, Roadmap, Betrieb und Cross-Repo-Koordination.',
    ],
    'metrics' => [
        ['value' => '4', 'text' => 'Repos mit klarer Verantwortlichkeit.'],
        ['value' => '1', 'text' => 'Kernloop: PGN, Analyse, Schlüsselstellung, Trainingskarte, Review.'],
        ['value' => '3', 'text' => 'Dienstebenen: Router, Search und Webspace-DB-API.'],
        ['value' => '0', 'text' => 'Secrets im Repo; produktive Zugangsdaten bleiben außerhalb versionierter Dateien.'],
    ],
    'repos' => [
        [
            'tag' => 'Hauptsystem',
            'name' => 'CaiLama',
            'href' => 'projects.php#cailama',
            'summary' => 'Schachanalyse, Training, Profile, Agent-CLI, PGN, Stockfish, OCR/Knowledge und DGT-nahe Workflows.',
            'points' => [
                'PTG erzeugt valide Artefakte, Trainingskarten, Kartentypen, Muster und erklärbare Priorität.',
                'Gewichtete Trainingspositionen, Coach-Sitzung on demand, Review-Gate, Planmodus, Hintergrund-Agenten und Benchmark-Events sind vorhanden.',
                'Offen sind der Review-Gate-Console-Flow, die Plan-Kaskade im AgentLoop, PTG-Live-Verifikation und erweiterte OCR/FEN-Qualitätsgates.',
            ],
        ],
        [
            'tag' => 'Router',
            'tag_class' => 'copper',
            'name' => 'CaiLama-LLM-Router',
            'href' => 'projects.php#router',
            'summary' => 'Generische Modellzugriffsschicht mit OpenAI-kompatibler API, Aliasen, Fallbacks und Betriebsmetriken.',
            'points' => [
                'Usage-Metriken, Benchmark-Export, Config-Hot-Reload und privacy-safe Logging sind umgesetzt.',
                'Der Router bleibt frei von Schachproduktlogik.',
                'Neue Arbeit entsteht erst durch Live-Smokes, Benchmark-Bedarf oder neue Backend-Profile.',
            ],
        ],
        [
            'tag' => 'Search',
            'tag_class' => 'red',
            'name' => 'CaiLama-Search',
            'href' => 'projects.php#search',
            'summary' => 'Search, Kontext, DWZ, Quellenverwaltung, Jobs, Goldsets, Observability und optionales semantisches Retrieval.',
            'points' => [
                'Lexical-vs-Hybrid-Benchmark liegt vor; semantic.enabled bleibt default false.',
                'Filter+Hybrid und Multi-Index-Response sind behoben; beide Modi erreichen Pass-Rate 1.0.',
                'Offen sind DWZ-Staging-Verifikation und Freigabeentscheidung für Hybrid auf größerem Eval.',
            ],
        ],
        [
            'tag' => 'Koordination',
            'tag_class' => 'moss',
            'name' => 'CaiLama-Master',
            'href' => 'operations.php#master',
            'summary' => 'Website, Roadmap, Ecosystem-Doku, Checkskripte, Webspace-DB-API-Fassade und LLM-freundliche Referenzen.',
            'points' => [
                'Der Master bleibt Koordination, nicht Runtime.',
                'Webseite nutzt einen privaten Smarty-App-Bereich außerhalb des Document Roots.',
                'Benchmark-Ergebnisse werden im Master zusammengeführt.',
            ],
        ],
    ],
    'next' => [
        ['title' => 'Jetzt', 'text' => 'Review-Gate in der Console abrunden, Plan-Kaskade in AgentLoop integrieren, PTG live prüfen und OCR/FEN-Gates weiter härten.'],
        ['title' => 'Danach', 'text' => 'Search-DWZ-Staging, semantische Freigabe auf größerem Eval, Datenschutz/Export und Qualitätsgates über PTG hinaus.'],
        ['title' => 'Später', 'text' => 'Spezialisierte Modelle erst nach Benchmark-Baseline, Datenfreigabe und Router-kompatibler Bereitstellung.'],
        ['title' => 'Betrieb', 'text' => 'Runtime und Website über secretfreie Skripte deployen, private Smarty-Abhängigkeit getrennt halten und Repos sauber für Kimi übergeben.'],
    ],
];
