<?php

declare(strict_types=1);

return [

    'heading' => 'Warum ich für diese Stelle passe',
    'intro' => 'Sie haben keine offizielle Stellenbeschreibung verlinkt, sondern eine Mail-Adresse. Deshalb habe ich die Anforderungen aus Ihrer Ausschreibung einzeln beantwortet — mit dem Nachweis daneben, und in zwei Fällen mit dem, was noch fehlt. Lieber eine offene Lücke als eine Behauptung, die im technischen Gespräch bricht.',

    'legend' => [
        'addressed' => 'Belegt',
        'partial' => 'Teilweise',
    ],

    'requirements' => [

        'laravel' => 'Solide Laravel-Erfahrung in Produktion — ausgeliefert und betrieben, nicht nur Seitenprojekte',

        'react' => 'React mit TypeScript auf Arbeitsniveau',

        'llm' => 'Erfahrung mit LLM-APIs und die Frage, wie man darauf etwas Verlässliches baut',

        'ownership' => 'Verantwortung übernehmen: der Code gehört dir',

        'tests' => 'Tests schreiben, und sich dafür interessieren, ob die Dinge funktionieren',

        'german' => 'Genug Deutsch, um mit deutschen Kunden zu sprechen',

        'location' => 'In der DACH-Region oder vergleichbar zuhause',

        'multitenancy' => 'Nice to have: Multi-Tenancy oder instanzbasiertes Deployment',

        'streaming' => 'Nice to have: Voice-Interfaces, Streaming, Echtzeit-UI',

    ],

    'evidence' => [

        'laravel' => 'Diese Website ist die Antwort — und sie ist keine Behauptung, sie ist der Quellcode, den Sie gerade benutzen. Laravel 13.34 auf PHP 8.4, Inertia 3, React 19, Tailwind 4, PHPUnit 12 als Testframework, CI mit Pint und PHPUnit, ausgeliefert auf Vercel in einem Docker-Container mit FrankenPHP. Mandantenfähigkeit pro Instanz, Migrationen, Factories, Seeder, ein Golden Dataset für den Assistenten. Ehrlicherweise: mein produktiver Backend-Schwerpunkt lag bisher auf Node/Express mit PostgreSQL und TypeORM, nicht auf Laravel. Diese Anwendung ist meine erste produktive Laravel-Basis — sie entsteht öffentlich, mit laufenden Tests und echtem Monitoring, statt in einem privaten Repository, das niemand je gesehen hat.',

        'react' => 'Zwei produktive Frontends mit React und TypeScript im Strict-Modus: PTD-Talento für das Cincinnatus Institute of Craftsmanship (React 18, Redux Toolkit, Material UI, Vite, eigener Design-System in Figma) und PCE-Agencia (React 19, Vite, Tailwind, React Router). In beiden habe ich neben der Oberfläche auch die Delivery geprägt — 74 gemergte Pull Requests und Issues, 18 Sprints, Migrationen idempotent machen, einen P1-IDOR schliessen, Rate-Limiting gegen Spam, Uploads nach Google Cloud Storage.',

        'llm' => 'Drei Ebenen, weil die Frage nicht «kann ich eine API aufrufen» lautet, sondern «was passiert, wenn sie ausfällt». Erstens produktiv: die Profilstärke-Bewertung in PTD-Talento ruft ein Modell über OpenRouter auf, mit 15 Sekunden Timeout, JSON-Schema-Prompt, defensiver Typ-Umwandlung — und einem deterministischen heuristischen Fallback, wenn kein Schlüssel vorhanden ist. Zweitens hier auf dieser Website: eine richtige Gateway-Schicht mit exponentiellem Backoff und Jitter, Retry-After wird respektiert, Circuit Breaker pro Backend, Fallback-Kette, Prompt-Cache pro Mandant, Guardrails auf Ein- und Ausgabe, und eine Zeile Observability pro Versuch. Drittens messbar: ein Golden Dataset, das ich per `php artisan evals:run` gegen die Live-Kette laufen lasse und das bei einem Prompt- oder Modellwechsel nicht-null exitet. Live-Zahlen aus der Tabelle `llm_runs` stehen weiter unten auf dieser Seite.',

        'ownership' => 'Bei PTD-Talento war ich Lead Tech eines Teams von vier: ich habe den Takt vorgegeben, über Reviews geführt und Releases geschnitten. Darüber hinaus betreibe ich alle eigenen Projekte selbst — vom ersten Commit bis zum Deployment, inklusive CI, Secrets, Server und Rollback. Diese Seite ist dasselbe Muster: ich habe sie entworfen, gebaut, getestet und ausgeliefert, ohne Zwischenebene.',

        'tests' => 'Auf dieser Website: Unit-Tests für die Backoff-Kurve, den Circuit Breaker, die Guardrails und den Cache-Key; Feature-Tests für Tenant-Auflösung, den Assistenten-Endpunkt und den SSE-Stream; Feature-Tests für jede Sprache, damit eine fehlende Übersetzung den Build bricht statt still zu verschwinden. Dazu CI, die Pint, PHPUnit und tsc laufen lässt. Ehrlich zur Historie: das Backend von PTD-Talento hatte keine Testsuite — ich habe das dort nicht nachgeholt und behaupte es hier auch nicht. PCE-Agencia hat eine vitest-Stufe in der GitHub-Actions-Pipeline.',

        'german' => 'Deutsch ist nicht meine Muttersprache — Spanisch ist es, Englisch rede ich auf B2. Diese Seite ist vollständig auf Deutsch, und der Assistent, mit dem Sie gerade sprechen können, antwortet auf Deutsch. Geschäftliches Deutsch schreibe ich; ich verstehe Fachtext und Produktdiskussionen. Was ich nicht behaupte: flüssige Kundengespräche auf dem Niveau, das ein Enterprise-Kunde in der DACH-Region erwartet. Das ist die eine Anforderung, die ich nicht voll erfülle, und ich sage das jetzt, statt es im Gespräch zu erklären. Ich arbeite aktiv daran, und wenn Sie ein Probegespräch auf Deutsch wollen, sagen Sie es mir — dann wissen Sie sofort, wo ich stehe.',

        'location' => 'Ich wohne in der Dominikanischen Republik (UTC−4), nicht in der DACH-Region. Das ist der Punkt, an dem Ihre Ausschreibung «Dach Region oder vergleichbar» sagt, und ich sage es lieber gleich: Ich bin es nicht. Was dafür gilt: Ich bin Schweizer Staatsbürger, es braucht also keine Arbeitsbewilligung und keine Vorlaufzeit für ein Visum. Ich kann von Tag eins arbeiten. Die Überlappung mit CET/CEST liegt am Vormittag, was die Zusammenarbeit in einem DACH-Team in der Praxis gut funktionieren lässt. Für Kunden- oder Investor-Termine vor Ort brauche ich Vorlauf und Planung, und das organisiere ich gerne — ich kenne das aus Projekten, bei denen ich zwischen zwei Zeitzonen gelernt habe, nicht aus einer theoretischen Annahme.',

        'multitenancy' => 'Diese Anwendung ist genau so gebaut. Ein Deployment pro Kunde, der Mandant wird aus der Umgebung aufgelöst und per Global Scope auf jede Query gesetzt, damit die Trennung nicht von Disziplin abhängt. `php artisan tenant:provision` erstellt eine neue Kundeninstanz in einem Befehl, migriert, seedet und gibt die Umgebungsvariablen aus, die für das neue Deployment gesetzt werden müssen. Das entspricht Ihrem Modell «jeder Kunde auf einer eigenen Instanz» — ohne es unnötig komplexer zu machen, als es ist.',

        'streaming' => 'Der Assistent auf dieser Seite streamt über Server-Sent Events, mit ehrlicher Herkunft: Sie sehen, welches Modell geantwortet hat, wie lange es gedauert hat und ob die Antwort aus dem Cache kam. Genau die Echtzeit-UI, die Sie als Nice to have nennen — hier gebaut, weil ich es zeigen wollte statt es zu behaupten. Wenn es in Ihrem AI Companion um Voice-Interfaces geht, wäre das der Bereich, in dem ich am schnellsten lerne, während Sie mir im laufenden Betrieb zeigen, was der Kunde wirklich braucht.',

    ],

    'label_tests' => 'Tests',
    'label_deploy' => 'Deploy',
    'label_retries' => 'Retries',
    'label_fallback' => 'Fallback',
    'label_evals' => 'Evals',
    'label_prs' => 'PRs',
    'label_sprints' => 'Sprints',
    'label_team' => 'Team',
    'label_permit' => 'Permit',
    'label_tz' => 'Zeitzone',
    'label_overlap' => 'CET-Überschneidung',

    'vercel' => 'Vercel (FrankenPHP)',
    'golden_dataset' => 'Golden Dataset',
    'yes' => 'Ja',
    'none_required' => 'Nicht nötig',
    'overlap' => 'Vormittag',

];
