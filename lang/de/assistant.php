<?php

declare(strict_types=1);

return [

    'system' => <<<'TXT'
        Du bist der Assistent auf der Portfolio-Website von Diego Härdi Santana und beantwortest Fragen
        über seinen Hintergrund, seinen Stack und diese Anwendung.

        Regeln, in dieser Reihenfolge:

        1. Antworte auf Deutsch, sachlich und konkret. Kein Marketington, keine Superlative ohne Beleg.
        2. Stütze dich ausschliesslich auf die Fakten unten. Wenn etwas nicht dasteht, sage, dass es das
           nicht tut, und schlage vor, wie Diego es am besten belegen kann.
        3. Nenne bei jeder technischen Aussage den konkreten Nachweis: Datei, Repository oder Commit.
        4. Erwähne die beiden offenen Punkte von selbst, wenn sie zur Frage passen: Deutsch ist nicht
           Muttersprache, und Diego wohnt in der Dominikanischen Republik, nicht in der DACH-Region.
        5. Keine Fragen am Ende. Antworte vollständig.
        6. Höchstens :maxChars Zeichen. Wenn die Antwort länger würde, kürze auf das Wesentliche.

        Fakten zu Diego:
        - Full-Stack-Entwickler, DevOps und Software Engineer.
        - Schweizer Staatsbürger, wohnhaft in Jarabacoa, Dominikanische Republik, UTC−4.
        - Für einen Schweizer Arbeitgeber ist keine Arbeitsbewilligung nötig.
        - Sprachen: Spanisch Muttersprache, Englisch B2, Deutsch B1 und im Ausbau.
        - Produktiver Backend-Schwerpunkt bisher: Node.js, Express, TypeORM, PostgreSQL, Docker,
          Google Cloud (App Engine, Cloud SQL, Cloud Storage), GitHub Actions.
        - Produktiver Frontend-Schwerpunkt: React 18 und 19, TypeScript strict, Redux Toolkit,
          Material UI, Tailwind, Vite.
        - Bei PTD-Talento des Cincinnatus Institute of Craftsmanship war er Lead Tech in einem
          Team von vier, über 18 Sprints, 74 gemergte Pull Requests und Issues.
        - Ausbildung: Técnico en Desarrollo y Administración de Aplicaciones Informáticas,
          Bootcamp Full-Stack am Cincinnatus Institute.
        - Diese Anwendung selbst: Laravel 13.34, PHP 8.4, Inertia 3, React 19, Tailwind 4, PHPUnit 12.

        Geförderte Projekte:
        :projects

        Stack in einem Satz: :stack
        TXT,

    'stack_line' => 'Laravel und PHP im Backend, React mit TypeScript strict im Frontend, PostgreSQL bzw. MySQL, Docker und CI/CD dazwischen, KI-Features mit Retry-, Fallback-, Cache- und Eval-Schicht.',

    'field' => [
        'question' => 'Frage',
        'conversation_id' => 'Konversations-ID',
    ],

    'error' => [
        'blocked' => 'Diese Eingabe wurde von einer Eingangsprüfung abgewiesen (Regel: :rule). Bitte formuliere die Frage ohne diesen Anteil.',
        'unavailable' => 'Gerade ist kein Modell erreichbar. Der Zuverlässigkeits-Layer hat es versucht und gemeldet — das ist genau der Fall, für den er gebaut wurde.',
    ],

    'intents' => [

        [
            'key' => 'laravel',
            'keywords' => ['laravel', 'php', 'stack', 'technolog', 'backend', 'migration', 'eloquent', 'artisan', 'pest', 'framework'],
            'answer' => 'Laravel ist in dieser Anwendung die produktive Basis: 13.34 auf PHP 8.4, Inertia 3 für die React-Anbindung, PHPUnit 12 als Testframework, Artisan-Befehle für die Mandantenbereitstellung. Ehrlich dazu: Mein produktiver Backend-Schwerpunkt lag bisher auf Node.js mit Express, TypeORM und PostgreSQL. Diese Anwendung ist meine erste produktive Laravel-Basis, und sie entsteht öffentlich — mit laufender Testsuite und echtem Monitoring, nicht in einem privaten Repository.',
        ],

        [
            'key' => 'react',
            'keywords' => ['react', 'typescript', 'tailwind', 'frontend', 'redux', 'vite', 'component', 'jsx'],
            'answer' => 'React und TypeScript sind meine zweite produktive Säule. Konkret: PTD-Talento mit React 18, Redux Toolkit, Material UI und eigenem Design-System, über 18 Sprints mitentwickelt, und PCE-Agencia mit React 19 und Vite. TypeScript läuft im Strict-Modus. In dieser Anwendung React 19 mit Inertia 3 und Tailwind 4 — die Typen sind über eine tsconfig mit noUncheckedIndexedAccess und noUnusedLocals erzwungen.',
        ],

        [
            'key' => 'llm',
            'keywords' => ['llm', 'ki', 'ai', 'künstliche', 'anthropic', 'openrouter', 'assistant', 'assistent', 'modell', 'prompt', 'zuverlässig', 'verlässlich', 'fallback', 'sicher'],
            'answer' => 'Beim Thema Zuverlässigkeit gibt es drei Ebenen. Erstens produktiv: Die Profilstärke-Bewertung in PTD-Talento ruft ein Modell über OpenRouter auf, mit 15 Sekunden Timeout, JSON-Schema-Prompt und einem deterministischen heuristischen Fallback, wenn kein Schlüssel vorhanden ist. Zweitens hier: ein Gateway mit exponentiellem Backoff und Jitter, Circuit Breaker pro Backend, Fallback-Kette, Prompt-Cache pro Mandant und Guardrails auf Ein- und Ausgabe. Drittens messbar: ein Golden Dataset, das ich per Artisan-Befehl gegen die Live-Kette laufen lasse und das bei einem Prompt- oder Modellwechsel nicht-null exitet. Jeder Versuch landet in der Tabelle llm_runs, und die Zahlen unten auf dieser Seite werden direkt daraus berechnet.',
        ],

        [
            'key' => 'tests',
            'keywords' => ['test', 'tests', 'testsuite', 'qualität', 'ci', 'pipeline', 'pint', 'abdeckung', 'coverage'],
            'answer' => 'Auf dieser Website gibt es Unit-Tests für die Backoff-Kurve, den Circuit Breaker, die Guardrails und die Cache-Schlüssel, Feature-Tests für die Mandantenauflösung, den Assistenten-Endpunkt und den SSE-Stream sowie Tests, die eine fehlende Übersetzung als Fehler behandeln. CI lässt Pint, PHPUnit und tsc laufen. Korrektur zu meiner Historie: Das Backend von PTD-Talento hatte keine Testsuite — das habe ich dort nicht nachgeholt und behaupte es deshalb nicht. PCE-Agencia hat eine vitest-Stufe in der Pipeline.',
        ],

        [
            'key' => 'multitenancy',
            'keywords' => ['mandant', 'mandanten', 'multi-tenancy', 'multitenancy', 'instanz', 'kunde', 'kunden', 'provision', 'bereitstellung', 'deployment'],
            'answer' => 'Die Anwendung bildet Instanz-Mandantenfähigkeit ab, wie Sie es beschreiben: ein Deployment pro Kunde. Der Mandant wird aus der Umgebung aufgelöst und per Global Scope auf jede Query gesetzt, damit die Trennung nicht von Disziplin abhängt. Der Befehl tenant:provision erstellt eine neue Kundeninstanz, migriert, seedet und gibt die Umgebungsvariablen aus, die für das neue Deployment gesetzt werden müssen. Das Aufsetzen neuer Kunden ist damit ein Befehl und kein Projekttag.',
        ],

        [
            'key' => 'streaming',
            'keywords' => ['stream', 'streaming', 'sse', 'echtzeit', 'realtime', 'real-time', 'voice', 'sprach', 'latenz', 'responsiv'],
            'answer' => 'Der Assistent streamt seine Antwort über Server-Sent Events, Stück für Stück. Sie sehen dabei live, welches Modell geantwortet hat, wie lange es gedauert hat und ob die Antwort aus dem Cache kam — die Herkunft wird nicht versteckt. Das ist die Echtzeit-UI, die Sie als Nice to Have nennen. Bei Voice-Interfaces wäre ich der Einsteiger: Ich kenne das Terrain von der Streaming-Seite, aber nicht von der Audio-Seite.',
        ],

        [
            'key' => 'experience',
            'keywords' => ['erfahrung', 'projekt', 'ptd', 'talent', 'cincinnatus', 'arbeit', 'lebenslauf', 'cv', 'lead', 'team'],
            'answer' => 'Meine wichtigste Rolle war Lead Tech bei PTD-Talento, dem Talent-Marketplace des Cincinnatus Institute of Craftsmanship: Team von vier, 18 Sprints, 74 gemergte Pull Requests und Issues. Konkret verantwortlich war ich unter anderem für einen P1-IDOR in einer Favoriten-Route, idempotente Migrationen, ein Audit-Trail, Rate-Limiting gegen Spam im Bewerbungsformular und den Upload von Dateien nach Google Cloud Storage. Dazu mein laufendes Studium als Técnico und der Full-Stack-Bootcamp am Cincinnatus Institute.',
        ],

        [
            'key' => 'german',
            'keywords' => ['deutsch', 'sprache', 'sprachen', 'kunde', 'kunden', 'verständlich', 'unterricht'],
            'answer' => 'Deutsch ist nicht meine Muttersprache. Spanisch ist es, Englisch rede ich auf B2. Diese Seite ist vollständig auf Deutsch und der Assistent, mit dem Sie gerade sprechen, antwortet auf Deutsch. Geschäftliches Deutsch schreibe ich und Fachtext verstehe ich ohne Mühe. Was ich nicht behaupte: flüssige Kundengespräche auf dem Niveau, das ein Enterprise-Kunde in der DACH-Region erwartet. Das ist die eine Anforderung aus Ihrer Ausschreibung, die ich nicht voll erfülle — ich sage es lieber jetzt als im Gespräch.',
        ],

        [
            'key' => 'location',
            'keywords' => ['standort', 'wohn', 'wohne', 'leben', 'lebenslauf', 'schweiz', 'schweizer', 'entfernung', 'anreise', 'vor ort', 'arbeitsbewilligung', 'visa', 'visum', 'pass', 'passport', 'remote', 'zeitzone', 'ansässig', 'domizil'],
            'answer' => 'Ich wohne in der Dominikanischen Republik, UTC−4, also nicht in der DACH-Region — das sage ich gleich, weil Sie es gefragt haben. Was gilt: Ich bin Schweizer Staatsbürger, es braucht also keine Arbeitsbewilligung und keine Vorlaufzeit für ein Visum. Die Überlappung mit CET/CEST liegt am Vormittag. Für Termine vor Ort plane ich Vorlauf ein. Wenn Sie mich für ein Gespräch in der Schweiz oder in der DACH-Region brauchen, sagen Sie mir Bescheid — ich bin bereit, dafür zu reisen.',
        ],

        [
            'key' => 'contact',
            'keywords' => ['kontakt', 'bewerbung', 'anschreiben', 'ansprechen', 'e-mail', 'mail', 'telefon', 'anrufen', 'linkedin', 'github'],
            'answer' => 'Direkter Weg ist eine E-Mail an diegosamuel042@gmail.com, oder Sie schreiben Daniel Intrinsa unter der Adresse, die Sie mir gegeben haben — ich bin an der Weiterleitung interessiert. Ich habe in zwei Wochen geantwortet bekommen, deshalb melde ich mich wahrscheinlich innerhalb von ein bis zwei Tagen. Der Quellcode dieser Website ist öffentlich, die Live-Instanz läuft auf Vercel.',
        ],

    ],

    'intents_fallback' => 'Diese Frage liegt ausserhalb dessen, was ich sicher beantworten kann. Ich kann zu Laravel, React, TypeScript, KI-Features, Tests, Mandantenfähigkeit, Streaming, meiner Berufserfahrung, meinen Sprachen und meinem Standort Auskunft geben — und zu allem anderen sage ich lieber nichts, was ich nicht belegen kann. Für alles andere: eine E-Mail an diegosamuel042@gmail.com, oder die Nummer aus Ihrer Ausschreibung direkt anrufen.',

];
