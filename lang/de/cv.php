<?php

declare(strict_types=1);

/*
 | Printable CV.
 |
 | The wording follows the Spanish original rather than the portfolio: same
 | claims, same level of detail, same order. Where the original lists a level or
 | a date, so does this.
 |
 | The notice exists because the original credits .NET, C#, SQL Server and
 | Angular, and the repository behind this portfolio contains none of them. It
 | is stated once, plainly, instead of quietly dropping the items or quietly
 | keeping them.
 */

return [

    'meta_description' => 'Lebenslauf von Diego Härdi: Full-Stack-Entwicklung mit MERN und .NET, Projekte, Berufserfahrung und Ausbildung.',

    'role' => 'Full Stack · MERN + .NET',
    'location' => 'La Rueda, Jarabacoa, Rep. Dom.',
    'availability' => 'Verfügbar: nachmittags',

    'print' => 'Als PDF drucken',

    'notice_title' => 'Hinweis zur Version',
    'notice_body' => 'Diese Version ist aus dem spanischen Original übersetzt. Die dort genannten Technologien (.NET, C#, SQL Server, Angular) sind im Portfolio-Repository nicht durch Quellcode belegt; mein dokumentierter Schwerpunkt liegt auf Node.js, Express, PostgreSQL, React und Laravel. Ich liste beide unverändert auf, statt Einträge stillschweigend zu streichen.',

    'sections' => [

        'profil' => [
            'title' => 'Profil',
            'body' => 'Full-Stack-Entwickler mit Schwerpunkt auf MERN und .NET. Projekte als Portfolio, Marktplätze und Dashboards — von der Oberfläche bis zum Deployment. Ausbildung in Softwareentwicklung sowie fünf Monate Berufserfahrung bei La Sirena, mit ausgeprägter Disziplin und Schnelligkeit im Lernen.',
        ],

        'stack' => [
            'title' => 'Technischer Stack',
            'items' => [
                'JavaScript',
                'TypeScript',
                'React',
                'Node.js',
                'Express',
                'Vite',
                'C#',
                '.NET',
                'SQL Server',
                'PostgreSQL',
                'MongoDB',
                'Tailwind',
                'HTML/CSS',
                'Docker',
                'Svelte',
                'Angular (Grundkenntnisse)',
                'Bootstrap',
            ],
        ],

        'idiomas' => [
            'title' => 'Sprachen',
            'items' => [
                ['label' => 'Spanisch', 'level' => 'Muttersprache'],
                ['label' => 'Englisch', 'level' => 'Grundkenntnisse / funktionsfähig'],
                ['label' => 'Deutsch', 'level' => 'Grundkenntnisse'],
            ],
        ],

        'enlaces' => [
            'title' => 'Links',
            'items' => [
                ['label' => 'GitHub', 'href' => 'https://github.com/dizzi1222'],
                ['label' => 'LinkedIn', 'href' => 'https://www.linkedin.com/in/diego-samuel-h%C3%A4rdi-santana-3a4343428/'],
                ['label' => 'E-Mail', 'href' => 'mailto:diegosamuel042@gmail.com'],
                ['label' => 'WhatsApp', 'href' => 'https://wa.me/18298216385'],
                ['label' => 'Telegram', 'href' => 'https://t.me/dizzi1222'],
                ['label' => 'Dev.to', 'href' => 'https://dev.to/dizzi1222'],
            ],
        ],

        'portfolio' => [
            'title' => 'Portfolio',
            'items' => [
                [
                    'name' => 'dhardi.dev',
                    'href' => 'https://dhardidev.vercel.app',
                    'description' => 'Persönliches Portfolio — Astro + React + Tailwind; 7 Projekte.',
                ],
                [
                    'name' => 'Terminal Portfolio',
                    'href' => 'https://portfolio-terminal-dhardi.vercel.app',
                    'description' => 'Terminal-Erlebnis — Svelte + TypeScript + Vite + i18n + Neovim-HUD.',
                ],
                [
                    'name' => 'PTD-Talento',
                    'href' => 'https://ptd-talento-frontend-dev-dot-cic-ptd-dev.ue.r.appspot.com',
                    'description' => 'Marktplatz für CIC — React + Node + PostgreSQL.',
                ],
                [
                    'name' => 'Laravel 13 (Portfolio)',
                    'href' => 'https://dhardi-laravel.vercel.app',
                    'description' => 'Laravel 13 + Inertia 3 + React 19 — Mandantenfähigkeit pro Instanz, LLM-Gateway mit Retry, Circuit Breaker, Fallback und Guardrails. 90 Tests.',
                ],
            ],
        ],

        'experiencia' => [
            'title' => 'Berufserfahrung',
            'items' => [
                [
                    'org' => 'PTD-Talento — Cincinnatus Institute of Craftsmanship',
                    'role' => 'Lead Tech (Team von vier)',
                    'period' => 'Jan – Okt 2026',
                    'bullets' => [
                        'Full-Stack-Entwicklung an einem Talent-Marktplatz: React 18 mit TypeScript, Express mit TypeORM und PostgreSQL, Docker und Google Cloud.',
                        'Über 18 Sprints und 74 gemergte Pull Requests und Issues; Entwicklungstaktung vorgegeben und Reviews geführt.',
                        'Sicherheitskorrektur auf P1-Niveau in einer Favoriten-Route, idempotente Migrationen, Audit-Trail und Rate-Limiting gegen Spam.',
                        'KI-gestütztes Scoring der Profilstärke über OpenRouter, mit Timeout und deterministischem heuristischem Fallback.',
                    ],
                ],
                [
                    'org' => 'Freelance / Eigene Projekte',
                    'role' => 'Full-Stack · DevOps',
                    'period' => '2024 – heute',
                    'bullets' => [
                        'Eigene Produkte vom ersten Commit bis zum laufenden Deployment, inklusive CI, Secrets und Betrieb.',
                    ],
                ],
                [
                    'org' => 'La Sirena — Jarabacoa, RD',
                    'role' => 'Verkaufsassistenz / Kundenservice',
                    'period' => 'Jan 2026 – Jun 2026',
                    'bullets' => [
                        'Einsatz des Tracker-Systems für die Erfassung und Kontrolle interner Filialprozesse.',
                        'Unterstützung bei Warenverlust und Inventur, einschliesslich Teilstückzahlen.',
                        'Drucken und Anbringen von Preisetiketten mit PowerK.',
                        'Auffüllen und ordentige Warenplatzierung im Regal.',
                        'Kasse und Kundenservice mit hoher Einsatzbereitschaft und Servicebereitschaft.',
                    ],
                ],
                [
                    'org' => 'TICS Service',
                    'role' => 'Ciber-Café / Papeterie — 360 Stunden',
                    'period' => null,
                    'bullets' => [
                        'Erfahrung im Zusammenhang mit der technischen Ausbildung in Entwicklung und Anwendung von Informationssystemen.',
                    ],
                ],
                [
                    'org' => 'Festival de las Flores — Jarabacoa',
                    'role' => 'Administrative Assistenz — 60 Stunden',
                    'period' => null,
                    'bullets' => [],
                ],
            ],
        ],

        'educacion' => [
            'title' => 'Ausbildung',
            'items' => [
                [
                    'org' => 'Liceo Técnico Luis Ernesto Gómez Uribe — Jarabacoa, RD',
                    'detail' => 'Técnico en Sistemas Informáticos.',
                    'period' => null,
                ],
                [
                    'org' => 'CIC (Cincinnatus) — Santiago de los Caballeros, RD',
                    'detail' => 'Softwareentwicklung, laufend.',
                    'period' => null,
                ],
            ],
        ],

    ],

    'footer_note' => 'Empfehlungsschreiben vorhanden — La Sirena',

];
