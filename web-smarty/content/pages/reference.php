<?php
declare(strict_types=1);

return [
    'active_nav' => 'reference',
    'title' => 'CaiLama - Referenz',
    'canonical_path' => '/reference.php',
    'meta_description' => 'Human- und LLM-freundliche Gesamt-Dokumentation des CaiLama-Ökosystems.',
    'footer_label' => 'CaiLama-Referenz',
    'footer_links' => [
        ['label' => 'Start', 'href' => 'index.php'],
    ],
    'hero' => [
        'eyebrow' => 'Human und LLM',
        'headline' => 'Gesamt-Doku des Ökosystems.',
        'lead' => 'Die Master-Doku ist die gemeinsame Referenz für Menschen und Agenten. HTML-Seiten sind lesbar gestaltet; Markdown und JSON sind als Nachschlagewerk für LLMs und Automatisierung gedacht.',
    ],
    'human_refs' => [
        ['title' => 'Projekte', 'text' => 'Detailstand von CaiLama, Router, Search und Master.', 'href' => 'projects.php'],
        ['title' => 'Architektur', 'text' => 'Schnittstellen, Flüsse, Analyse- und Trainingskette.', 'href' => 'architecture.php'],
        ['title' => 'Startseite', 'text' => 'Trainingsfokus, Kernloop, Nicht-Ziele und Differenzierung.', 'href' => 'index.php'],
        ['title' => 'Roadmap', 'text' => 'Priorisierte Umsetzung aus dem aktuellen Statusplan.', 'href' => 'roadmap.php'],
        ['title' => 'Betrieb', 'text' => 'Webspace, Checks, Master-Regeln und Qualität.', 'href' => 'operations.php'],
        ['title' => 'Status', 'text' => 'Aktueller Stand des Ökosystems mit Repo- und Plattformübersicht.', 'href' => 'status.php'],
    ],
    'llm_refs' => [
        ['tag' => 'Text', 'tag_class' => 'blue', 'title' => 'llms.txt', 'text' => 'Minimaler Einstiegspunkt für LLM-Crawler und Agenten.', 'href' => 'llms.txt'],
        ['tag' => 'Markdown', 'title' => 'Ecosystem Reference', 'text' => 'LLM-freundliche Gesamtreferenz mit Repos, Rollen, Schnittstellen, Roadmap und Regeln.', 'href' => 'ecosystem-reference.md'],
        ['tag' => 'JSON', 'tag_class' => 'copper', 'title' => 'Machine Data', 'text' => 'Strukturierte Maschinenreferenz für Automatisierung, Agenten und Validierung.', 'href' => 'data/ecosystem.json'],
    ],
];
