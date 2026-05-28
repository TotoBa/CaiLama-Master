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
                'PTG-Live-Verifikation ist durchgeführt und als guarded Smoke-Skript verfügbar.',
                'Legal-Move-/Brettwahrheit-Daten erreichen Review-, Coach- und Benchmark-Artefakte.',
                'RAG-Provenienz, OCR/FEN-Gates sowie Analyse-/Training-Gates sind umgesetzt.',
                'Retention und Profilbindung für dateibasierte Trainingskarten und Review-Historien abschließen.',
                'Router-/CaiLama-Benchmarkmetriken automatisch in das geschützte Website-Feedback übernehmen.',
            ],
        ],
        [
            'tag' => 'Datenschutz',
            'tag_class' => 'red',
            'title' => 'Leistungsprofile',
            'items' => [
                'Profil-Export und bestätigte Profil-Löschung sind umgesetzt.',
                'Retention-Konzept für Trainings- und Review-Daten definieren und an dateibasierte Stores binden.',
                'Keine privaten Partien oder Kommentare für Modelltraining ohne ausdrückliche Freigabe verwenden.',
            ],
        ],
        [
            'tag' => 'Search',
            'tag_class' => 'blue',
            'title' => 'RAG und Provenienz',
            'items' => [
                'Quellenformat ist im Agent-/Researcher-Pfad vereinheitlicht.',
                'Antworten führen Titel, Quelle/URL oder Herkunft, Stand/Freshness, Verwendungszweck und Unsicherheit.',
                'Source-Quality-Kennzahlen erfassen Provenienz, Quellenanzahl, Domain-Diversität als Count, Freshness-Signale und Herkunftstypen ohne URL-/Domain-Export.',
                'DWZ-v2-Liveimport ist verifiziert; Namens- und Vereinssuchen liefern Spieler, Verein und Bezirk.',
                'Source-Registry-Metadaten trennen offene Volltextquellen, offizielle Referenzen und rechtekritische Kandidaten.',
                'Source-Policy-Gates für ungeklärte kommerzielle oder UGC-lastige Quellen schärfen.',
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
                'Automatisch bewertbare Struktur-, Tool-, Fehler- und Dauerfälle per Feedback-Agent schließen.',
                'Router-Benchmark-Exports einsammeln.',
                'OCR/FEN-False-Positive- und Validitätsgates dokumentiert halten.',
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
