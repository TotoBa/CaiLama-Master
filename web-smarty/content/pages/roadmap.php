<?php
declare(strict_types=1);

return [
    'active_nav' => 'roadmap',
    'title' => 'CaiLama - Roadmap',
    'canonical_path' => '/roadmap.php',
    'meta_description' => 'Roadmap und nächste Integrationsschritte für das CaiLama-Ökosystem.',
    'footer_label' => 'CaiLama-Roadmap',
    'footer_links' => [
        ['label' => 'Betrieb und Qualität', 'href' => 'operations.php'],
    ],
    'hero' => [
        'eyebrow' => 'Priorisierung',
        'headline' => 'Roadmap: erst Produktloop, dann Plattform.',
        'lead' => 'Der Ausbau folgt einer harten Reihenfolge. Erst muss der Trainingsloop reproduzierbar, messbar und verständlich sein. Danach werden Live-Router, Search/RAG, Webspace-Synchronisation, Benchmarking und später spezialisierte Modelle systematisch erweitert.',
    ],
    'phases' => [
        [
            'tag' => 'CaiLama',
            'title' => 'Produktloop härten',
            'items' => [
                'Review-Gate in der interaktiven Console mit Unicode-Brett pro Kandidat und finalem Submit abrunden.',
                'Plan-Kaskade in AgentLoop integrieren, damit bestätigte Pläne die Ausführung steuern.',
                'PTG-Live-Verifikation bewusst gegen Router prüfen und Ergebnisse in Benchmarks zurückführen.',
                'OCR/FEN-Gates ohne geratene FENs weiter härten.',
                'Qualitätsgates über PTG hinaus vereinheitlichen.',
            ],
        ],
        [
            'tag' => 'Datenschutz',
            'tag_class' => 'red',
            'title' => 'Leistungsprofile',
            'items' => [
                'Vorhandenes Datenschutzkonzept in Export- und Löschpfade überführen.',
                'Export und Löschung pro Profil vorbereiten.',
                'Retention-Konzept für Trainings- und Review-Daten definieren.',
                'Keine privaten Partien oder Kommentare für Modelltraining ohne ausdrückliche Freigabe verwenden.',
            ],
        ],
        [
            'tag' => 'Search',
            'tag_class' => 'blue',
            'title' => 'RAG und Provenienz',
            'items' => [
                'Quellenformat überall vereinheitlichen.',
                'Antworten mit Titel, Quelle/URL, Stand/Freshness, Verwendet-für und Unsicherheit ausgeben.',
                'DWZ-Staging-Test bewusst verifizieren.',
                'Hybrid bleibt trotz behobener Korrektheitsfehler bis zur Produktentscheidung default-off.',
            ],
        ],
        [
            'tag' => 'Benchmarks',
            'tag_class' => 'copper',
            'title' => 'Master-Ablage',
            'items' => [
                'Search-Benchmark lexical vs hybrid und PTG-Offline-Baseline im Master dokumentiert halten.',
                'Modellrollen-Hypothese über geschütztes Website-Feedback messen.',
                'Router-Benchmark-Exports einsammeln.',
                'OCR/FEN-False-Positive-Gates definieren.',
                'Ergebnisformat in docs/benchmark-results/ standardisieren.',
            ],
        ],
        [
            'tag' => 'Modelle',
            'tag_class' => 'moss',
            'title' => 'Späterer Modellhebel',
            'items' => [
                'Spezialisiertes LLM-Training erst nach Benchmark-Baseline.',
                'Eval-, Test- und Trainingsdaten strikt trennen.',
                'Nur freigegebene, synthetische oder anonymisierte Daten verwenden.',
                'Modelle ausschließlich über den Router-Vertrag bereitstellen.',
            ],
        ],
    ],
];
