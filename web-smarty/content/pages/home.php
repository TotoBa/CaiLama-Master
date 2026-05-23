<?php
declare(strict_types=1);

return [
    'active_nav' => 'home',
    'title' => 'CaiLama - Trainingswerkstatt für Schach',
    'canonical_path' => '/',
    'meta_description' => 'CaiLama ist eine Trainingswerkstatt für Schachanalyse, PGN-Training, Stockfish-Grounding, LLM-Kommentierung, Search/RAG und DGT-nahe Workflows.',
    'footer_label' => 'CaiLama',
    'footer_links' => [
        ['label' => 'Roadmap', 'href' => 'roadmap.php'],
    ],
    'hero' => [
        'eyebrow' => 'Deine Analyse. Deine Daten. Dein Training.',
        'headline' => 'Vom PGN zur Trainingsaufgabe.',
        'lead' => 'CaiLama ist eine Trainingswerkstatt für ernsthafte Schachverbesserung. Das System verbindet PGN-Import, Stockfish- und Heuristik-Grounding, LLM-Kommentierung, gewichtete Schlüsselstellungen, Trainingskarten, Review-Rückfluss und später DGT-nahes Bretttraining.',
        'actions' => [
            ['label' => 'Kernloop ansehen', 'href' => '#kernloop', 'class' => 'primary'],
            ['label' => 'Projektstand', 'href' => 'projects.php'],
            ['label' => 'GitHub', 'href' => 'https://github.com/TotoBa/CaiLama'],
        ],
    ],
    'timeline' => [
        ['title' => '1. Import', 'text' => 'PGN oder Plattformpartie aufnehmen und gültige Ausgangsdaten sichern.'],
        ['title' => '2. Analyse', 'text' => 'Stockfish, Brettwahrheit, Legal-Move-Tags und Heuristiksignale statt ungeprüfter Modellbehauptungen.'],
        ['title' => '3. Aufgaben', 'text' => 'Schlüsselstellungen, Gewichtungen, Trainingsfragen, Karten, kommentierte PGN und Trainings-JSON erzeugen.'],
        ['title' => '4. Coach', 'text' => 'Eine konkrete Trainingseinheit erst bei Bedarf auswählen, mit Unicode-Brett anzeigen und bei verbundenem DGT-Brett zum Aufstellen auffordern.'],
    ],
    'capabilities' => [
        ['title' => 'PGN und Analyse', 'text' => 'Partien werden verarbeitet, mit Stockfish, Brettwahrheit, Legal-Move-Tags und Heuristiksignalen angereichert und als gültige Artefakte erhalten.'],
        ['title' => 'Schlüsselstellungen', 'text' => 'Aus kritischen Momenten entstehen gewichtete Trainingspositionen statt bloßer Fließtext-Kommentare.'],
        ['title' => 'Trainingskarten', 'text' => 'Fehler, Muster, Kartentypen und Prioritäten werden strukturiert und für Wiederholung vorbereitet.'],
        ['title' => 'Review-Rückfluss', 'text' => 'Gelöste und ungelöste Karten beeinflussen spätere Trainingsauswahl und Coach-Sitzungen.'],
        ['title' => 'LLM über Router', 'text' => 'Modellzugriff läuft über Rollen und Aliase, nicht über hart verdrahtete Provider.'],
        ['title' => 'Search/RAG-Kontext', 'text' => 'Schachquellen, DWZ-Daten und eigene Wissensbestände werden kontrolliert über CaiLama-Search angebunden.'],
    ],
];
