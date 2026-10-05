<?php

declare(strict_types=1);

return [

    'heading' => 'Por qué encajo en esta vacante',
    'intro' => 'No enlazó una descripción oficial de puesto, sino una dirección de correo. Por eso respondí los requisitos de su anuncio uno por uno — con la evidencia al lado, y en dos casos con lo que me falta. Prefiero una brecha abierta a una afirmación que se rompa en la entrevista técnica.',

    'legend' => [
        'addressed' => 'Cubierto',
        'partial' => 'Parcial',
    ],

    'requirements' => [

        'laravel' => 'Experiencia sólida en Laravel en producción — entregado y operado, no solo proyectos personales',

        'react' => 'React con TypeScript a nivel laboral',

        'llm' => 'Experiencia con APIs de LLM, y cómo construir algo confiable sobre ellas',

        'ownership' => 'Asumir responsabilidad: el código es tuyo',

        'tests' => 'Escribir tests, e interesarse por si las cosas funcionan',

        'german' => 'Suficiente alemán para hablar con clientes alemanes',

        'location' => 'Estar en la región DACH o algo comparable',

        'multitenancy' => 'Nice to have: multi-tenancy o despliegue por instancia',

        'streaming' => 'Nice to have: interfaces de voz, streaming, UI en tiempo real',

    ],

    'evidence' => [

        'laravel' => 'Este sitio es la respuesta — y no es una afirmación, es el código que está usando ahora mismo. Laravel 13.34 sobre PHP 8.4, Inertia 3, React 19, Tailwind 4, PHPUnit 12 como framework de tests, CI con Pint y PHPUnit, desplegado en Vercel dentro de un contenedor Docker con FrankenPHP. Multi-tenancy por instancia, migraciones, factories, seeders, y un golden dataset para el asistente. Con honestidad: mi foco backend en producción ha sido Node.js con Express, TypeORM y PostgreSQL, no Laravel. Esta aplicación es mi primera base productiva en Laravel — nace pública, con tests corriendo y monitoreo real, en lugar de en un repo privado que nadie ha visto nunca.',

        'react' => 'Dos frontends productivos con React y TypeScript estricto: PTD-Talento para el Cincinnatus Institute of Craftsmanship (React 18, Redux Toolkit, Material UI, Vite, design system propio en Figma) y PCE-Agencia (React 19, Vite, Tailwind, React Router). En ambos también marqué la entrega: 74 pull requests e issues mergeados, 18 sprints, migraciones idempotentes, cierre de un P1 IDOR, rate-limiting anti-spam y subidas a Google Cloud Storage.',

        'llm' => 'Tres niveles, porque la pregunta no es «sé llamar a una API» sino «qué pasa cuando se cae». Primero, en producción: el scoring de fuerza de perfil en PTD-Talento llama a un modelo vía OpenRouter, con timeout de 15 segundos, prompt con JSON Schema y fallback heurístico determinista cuando no hay llave. Segundo, aquí en este sitio: una capa gateway real con backoff exponencial y jitter, respeto del Retry-After, circuit breaker por backend, cadena de fallback, caché de prompts por tenant, guardrails de entrada y salida, y una fila de observabilidad por intento. Tercero, medible: un golden dataset que corro contra la cadena viva con `php artisan evals:run`, y que sale distinto de cero si un cambio de prompt o de modelo rompe algo. Las cifras en vivo de la tabla `llm_runs` están más abajo en esta página.',

        'ownership' => 'En PTD-Talento fui Lead Tech de un equipo de cuatro: fijé el ritmo, conduje las revisiones y corté releases. Además opero todos mis proyectos propios: del primer commit al deployment, incluyendo CI, secrets, servidores y rollback. Este sitio es el mismo patrón: lo diseñé, lo construí, lo probé y lo desplegué, sin capas intermedias.',

        'ownership_note' => '',

        'tests' => 'En este sitio: tests unitarios para la curva de backoff, el circuit breaker, los guardrails y las claves de caché; tests feature para la resolución de tenant, el endpoint del asistente y el stream SSE; y tests que hacen fallar el build si falta una traducción. Además, una CI que corre Pint, PHPUnit y tsc. Honesto con la historia: el backend de PTD-Talento no tenía suite de tests — no lo remedié ahí y tampoco lo reclamo aquí. PCE-Agencia sí tiene una etapa de vitest en su pipeline de GitHub Actions.',

        'german' => 'El alemán no es mi lengua materna — el español sí, y hablo inglés en B2. Este sitio está completo en alemán, y el asistente con el que está hablando ahora responde en alemán. Escribo alemán de negocios y entiendo texto técnico y discusión de producto sin esfuerzo. Lo que no afirmo: conversaciones fluidas con clientes al nivel que espera un cliente enterprise en la región DACH. Ese es el único requisito de su anuncio que no cumplo por completo, y lo digo ahora en lugar de explicarlo en la entrevista. Estoy trabajándolo activamente, y si quiere una entrevista de prueba en alemán, dígamelo — así sabrá de inmediato dónde estoy.',

        'location' => 'Vivo en la República Dominicana (UTC−4), no en la región DACH. Ese es el punto donde su anuncio dice «región de los Alpes o comparable», y prefiero decirlo de entrada: no lo estoy. Lo que sí aplica: soy ciudadano suizo, así que no hace falta permiso de trabajo ni previsibilidad para un visado. Puedo trabajar desde el primer día. El solapamiento con CET/CEST es por la mañana, lo que hace que trabajar con un equipo DACH funcione bien en la práctica. Para reuniones o sesiones con inversores en persona necesito antelación y planificación, y lo organizo con gusto — lo sé por haber trabajado entre dos zonas horarias, no por suposición teórica.',

        'multitenancy' => 'Esta aplicación está construida exactamente así. Un deployment por cliente, el tenant se resuelve desde el entorno y se aplica con un Global Scope a cada query, para que el aislamiento no dependa de la disciplina. `php artisan tenant:provision` crea una instancia nueva de cliente en un comando, migra, seedea e imprime las variables de entorno que hay que setear en el nuevo deployment. Eso corresponde a su modelo de «cada cliente en su propia instancia» sin hacerlo más complejo de lo que es.',

        'streaming' => 'El asistente de este sitio hace streaming de su respuesta por Server-Sent Events, con procedencia honesta: usted ve qué modelo respondió, cuánto tardó y si la respuesta vino de caché. Exactamente la UI en tiempo real que usted menciona como nice to have — construida aquí porque prefiero mostrarla a afirmarla. Si en su AI Companion se trata de interfaces de voz, ese es el área en la que más rápido aprendo mientras usted me muestra en operación real qué necesita el cliente.',

    ],

    'label_tests' => 'Tests',
    'label_deploy' => 'Entrega',
    'label_retries' => 'Reintentos',
    'label_fallback' => 'Fallback',
    'label_evals' => 'Evals',
    'label_prs' => 'PRs',
    'label_sprints' => 'Sprints',
    'label_team' => 'Equipo',
    'label_permit' => 'Permiso',
    'label_tz' => 'Zona horaria',
    'label_overlap' => 'Solapamiento CET',

    'vercel' => 'Vercel (FrankenPHP)',
    'golden_dataset' => 'Golden dataset',
    'yes' => 'Sí',
    'none_required' => 'No aplica',
    'overlap' => 'Mañana',

];
