<?php

declare(strict_types=1);

return [

    'brand' => 'DH',
    'language_label' => 'Sprache',
    'skip_to_content' => 'Zum Inhalt springen',

    'nav' => [
        'fit' => 'Passform',
        'experience' => 'Erfahrung',
        'projects' => 'Projekte',
        'stack' => 'Stack',
        'assistant' => 'Assistent',
        'contact' => 'Kontakt',
    ],

    'cv' => 'Lebenslauf',

    'available' => 'Offen für neue Projekte',

    'sections' => [

        'fit' => [
            'eyebrow' => 'Anforderungen',
            'heading' => 'Warum ich für diese Stelle passe',
        ],

        'experience' => [
            'eyebrow' => 'Berufserfahrung',
            'heading' => 'Erfahrung',
            'subtitle' => 'Rollen, Verantwortungsbereich und was tatsächlich geliefert wurde.',
            'highlights' => 'Verantwortung',
            'duration_label' => 'Dauer',
            'current' => 'Heute',
            'remote' => 'Remote',
            'months' => 'Monate',
            'no_highlights' => 'Keine weiteren Angaben hinterlegt.',
        ],

        'projects' => [
            'eyebrow' => 'Arbeit',
            'heading' => 'Projekte',
            'subtitle' => 'Jedes mit Stack, Status und dem Nachweis, der die Behauptung trägt.',
            'live' => 'Live',
            'source' => 'Quelltext',
            'view' => 'Fallstudie',
            'problem' => 'Ausgangslage',
            'approach' => 'Vorgehen',
            'highlights' => 'Ergebnis',
            'metrics' => 'Kennzahlen',
            'featured' => 'Im Fokus',
        ],

        'stack' => [
            'eyebrow' => 'Belege',
            'heading' => 'Stack mit Nachweis',
            'subtitle' => 'Jeder Eintrag verweist auf ein Repository oder einen Commit, nicht auf eine Behauptung.',
        ],

        'skills' => [
            'heading' => 'Technologien',
            'subtitle' => 'Mit dem Artefakt daneben, das die Tiefe belegt.',
        ],

        'certifications' => [
            'eyebrow' => 'Ausbildung',
            'heading' => 'Ausbildung und Zertifikate',
            'subtitle' => 'Abgeschlossenes und Laufendes getrennt ausgewiesen.',
        ],

        'contact' => [
            'eyebrow' => 'Kontakt',
            'heading' => 'Kontakt',
            'subtitle' => 'Kein Formular, kein Tracking. Direkt, wie Sie es in Ihrer Ausschreibung formuliert haben.',
            'email_label' => 'E-Mail',
            'cv_label' => 'Lebenslauf als PDF',
            'primary_cta' => 'E-Mail schreiben',
        ],

        'footer' => [
            'built_with' => 'Gebaut mit Laravel 13, Inertia 3, React 19, TypeScript, Tailwind 4 und Pest. Ausgeliefert auf Vercel in einem Docker-Container mit FrankenPHP.',
            'source' => 'Quelltext dieser Website',
            'rights' => 'Alle Rechte vorbehalten.',
        ],

    ],

    'status' => [
        'shipped' => 'Ausgeliefert',
        'in_progress' => 'In Arbeit',
        'archived' => 'Archiviert',
    ],

    'cert_status' => [
        'completed' => 'Abgeschlossen',
        'in_progress' => 'Laufend',
    ],

    'profile' => [
        'role' => 'Rolle',
        'born' => 'Geboren',
    ],

    // Labels for the `metrics` object of a project. The keys are stable slugs;
    // the text is translated, so the same project renders correctly in all
    // three languages without the content file knowing any of them.
    'metrics' => [
        'languages' => 'Sprachen',
        'backends' => 'Backends',
        'tenancy' => 'Mandantenmodell',
        'prs' => 'PRs und Issues',
        'sprints' => 'Sprints',
        'team' => 'Team',
        'epics' => 'Epicas',
        'resources' => 'REST-Ressourcen',
        'ci_stages' => 'CI-Stufen',
        'tables' => 'Tabellen',
        'deploy' => 'Auslieferung',
        'migrations' => 'Migrationen',
        'windows' => 'Fenster',
        'packages' => 'Pakete',
        'rebuild' => 'Rebuild',
        'tools' => 'Tools',
        'ssrf' => 'SSRF-Schutz',
    ],

    'project' => [
        'back' => 'Zurück zur Übersicht',
        'other' => 'Weitere Projekte',
        'not_found' => 'Dieses Projekt existiert nicht.',
    ],

    'assistant' => [
        'eyebrow' => 'Live-Demo',
        'heading' => 'Fragen Sie den Assistenten',
        'subtitle' => 'Der Assistent antwortet aus den Daten dieser Website und nennt dabei immer, welches Modell geantwortet hat. Er läuft über die gleiche Gateway-Schicht wie ein produktives Feature: Retry, Circuit Breaker, Fallback, Cache, Guardrails.',
        'placeholder' => 'Zum Beispiel: Wie ist die Mandantenfähigkeit gelöst?',
        'send' => 'Fragen',
        'sending' => 'Antwortet …',
        'clear' => 'Neue Unterhaltung',
        'suggestions' => [
            'Warum Laravel und nicht Node?',
            'Wie funktioniert die Zuverlässigkeitsschicht?',
            'Wie werden neue Kunden aufgesetzt?',
            'Sprichst du Deutsch?',
        ],
        'meta_provider' => 'Modell',
        'meta_latency' => 'Dauer',
        'meta_cached' => 'aus Cache',
        'meta_cost' => 'Kosten',
        'disclaimer' => 'Der Assistent kann irren. Er wird aus den Inhalten dieser Website gespeist und erfindet keine Fakten, die dort nicht stehen — aber prüfen Sie Aussagen über mich bitte gegen mein Repository.',
        'empty' => 'Noch keine Frage gestellt.',
    ],

    'telemetry' => [
        'eyebrow' => 'Betrieb',
        'heading' => 'Was die Zuverlässigkeitsschicht misst',
        'subtitle' => 'Live aus der Tabelle llm_runs. Keine geschätzten Zahlen: Jeder Wert ist eine Abfrage über die Versuche, die diese Anwendung selbst protokolliert hat.',
        'runs' => 'Versuche',
        'since' => 'seit',
        'metrics' => [
            'success' => 'Erfolgsquote',
            'cache_hit' => 'Cache-Treffer',
            'blocked' => 'Von Guardrails blockiert',
            'fallback' => 'Antwort über Fallback',
            'p50' => 'Latenz p50',
            'p95' => 'Latenz p95',
            'max' => 'Latenz maximal',
            'cost_total' => 'Kosten gesamt',
            'cost_avg' => 'Kosten pro Versuch',
        ],
        'evals' => [
            'heading' => 'Golden Dataset',
            'note' => 'Regelmässig gegen die Live-Kette laufen gelassen. Ein Prompt- oder Modellwechsel, der die erwarteten Angaben verändert, fällt hier auf — nicht erst im Kundengespräch.',
            'cases' => 'Fälle',
            'measured' => 'gemessen',
            'pass_rate' => 'Bestehensquote',
            'last_run' => 'Letzter Lauf',
        ],
        'empty' => 'Noch keine Daten vorhanden. Diese Tabelle füllt sich, sobald der Assistent benutzt wird.',
    ],

    'common' => [
        'back' => 'Zurück',
        'close' => 'Schliessen',
        'loading' => 'Lädt …',
        'retry' => 'Erneut versuchen',
        'unknown' => 'Unbekannt',
    ],

];
