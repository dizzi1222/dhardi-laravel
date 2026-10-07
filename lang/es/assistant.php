<?php

declare(strict_types=1);

return [

    'system' => <<<'TXT'
        Eres el asistente del sitio de portafolio de :author y respondes preguntas sobre su
        trayectoria, su stack y esta aplicación.

        Reglas, en este orden:

        1. Responde en español, de forma directa y concreta. Sin marketing, sin superlativos sin evidencia.
        2. Apóyate únicamente en los hechos indicados abajo. Si algo no está ahí, dilo, y sugiere cómo
           Diego podría demostrarlo mejor.
        3. En cada afirmación técnica, nombra la evidencia concreta: archivo, repositorio o commit.
        4. Menciona tú mismo los dos puntos abiertos cuando quepan en la pregunta: el alemán no es su
           lengua materna, y Diego vive en la República Dominicana, no en la región DACH.
        5. No hagas preguntas al final. Responde por completo.
        6. Máximo :maxChars caracteres. Si la respuesta fuera más larga, córtala a lo esencial.

        Datos sobre Diego:
        - Full-stack developer, DevOps y software engineer.
        - Ciudadano suizo, residente en Jarabacoa, República Dominicana, UTC−4.
        - Para un empleador suizo no hace falta permiso de trabajo.
        - Idiomas: español nativo, inglés B2, alemán B1 y en formación.
        - Foco backend productivo hasta ahora: Node.js, Express, TypeORM, PostgreSQL, Docker,
          Google Cloud (App Engine, Cloud SQL, Cloud Storage), GitHub Actions.
        - Foco frontend productivo: React 18 y 19, TypeScript estricto, Redux Toolkit,
          Material UI, Tailwind, Vite.
        - En PTD-Talento del Cincinnatus Institute of Craftsmanship fue Lead Tech en un equipo de
          cuatro, a lo largo de 18 sprints y 74 pull requests e issues mergeados.
        - Formación: Técnico en Desarrollo y Administración de Aplicaciones Informáticas,
          bootcamp full-stack en el Cincinnatus Institute.
        - Esta misma aplicación: Laravel 13.34, PHP 8.4, Inertia 3, React 19, Tailwind 4, PHPUnit 12.

        Proyectos destacados:
        :projects

        Stack en una frase: :stack
        TXT,

    'stack_line' => 'Laravel y PHP en el backend, React con TypeScript estricto en el frontend, PostgreSQL o MySQL, Docker y CI/CD en el medio, y features de IA con capa de reintentos, fallback, caché y evals.',

    'field' => [
        'question' => 'Pregunta',
        'conversation_id' => 'ID de conversación',
    ],

    'error' => [
        'blocked' => 'Esta entrada fue rechazada por una validación de entrada (regla: :rule). Reformula la pregunta sin esa parte.',
        'unavailable' => 'Ahora mismo no hay ningún modelo disponible. La capa de fiabilidad lo intentó y lo reportó — ese es exactamente el caso para el que existe.',
    ],

    'intents' => [

        [
            'key' => 'laravel',
            'keywords' => ['laravel', 'php', 'stack', 'tecnolog', 'backend', 'migration', 'eloquent', 'artisan', 'pest', 'framework'],
            'answer' => 'Laravel es la base productiva de esta aplicación: 13.34 sobre PHP 8.4, Inertia 3 para la integración con React, PHPUnit 12 como framework de tests y comandos Artisan para aprovisionar tenants. Con honestidad: mi foco backend en producción ha sido Node.js con Express, TypeORM y PostgreSQL. Esta aplicación es mi primera base productiva en Laravel, y nace pública, con la suite de tests corriendo y monitoreo real, no en un repositorio privado.',
        ],

        [
            'key' => 'react',
            'keywords' => ['react', 'typescript', 'tailwind', 'frontend', 'redux', 'vite', 'componente', 'jsx'],
            'answer' => 'React y TypeScript son mi segunda columna productiva. En concreto: PTD-Talento con React 18, Redux Toolkit, Material UI y design system propio, desarrollado a lo largo de 18 sprints, y PCE-Agencia con React 19 y Vite. TypeScript corre en modo estricto. En esta aplicación, React 19 con Inertia 3 y Tailwind 4, con los tipos forzados por una tsconfig que activa noUncheckedIndexedAccess y noUnusedLocals.',
        ],

        [
            'key' => 'llm',
            'keywords' => ['llm', 'ia', 'ai', 'inteligencia', 'anthropic', 'openrouter', 'assistant', 'asistente', 'modelo', 'prompt', 'confiable', 'fiable', 'fallback', 'seguro'],
            'answer' => 'El tema de la confiabilidad tiene tres niveles. Primero, en producción: el scoring de fuerza de perfil en PTD-Talento llama a un modelo vía OpenRouter, con timeout de 15 segundos, prompt con JSON Schema y un fallback heurístico determinista cuando no hay llave. Segundo, aquí: un gateway con backoff exponencial y jitter, circuit breaker por backend, cadena de fallback, caché de prompts por tenant y guardrails de entrada y salida. Tercero, medible: un golden dataset que corro contra la cadena viva con un comando Artisan y que sale distinto de cero si un cambio de prompt o de modelo rompe algo. Cada intento queda en la tabla llm_runs, y las cifras de esta página se calculan directamente desde ahí.',
        ],

        [
            'key' => 'tests',
            'keywords' => ['test', 'tests', 'suite', 'calidad', 'ci', 'pipeline', 'pint', 'cobertura', 'coverage'],
            'answer' => 'En este sitio hay tests unitarios para la curva de backoff, el circuit breaker, los guardrails y las claves de caché; tests feature para la resolución de tenant, el endpoint del asistente y el stream SSE; y tests que hacen fallar el build si falta una traducción. Además, una CI que corre Pint, PHPUnit y tsc. Corrección sobre mi historia: el backend de PTD-Talento no tenía suite de tests — no lo remedié ahí y por eso no lo reclamo. PCE-Agencia sí tiene una etapa de vitest en su pipeline.',
        ],

        [
            'key' => 'multitenancy',
            'keywords' => ['tenant', 'multi-tenancy', 'multitenancy', 'instancia', 'cliente', 'clientes', 'provision', 'aprovisionar', 'deployment'],
            'answer' => 'La aplicación modela multi-tenancy por instancia, tal como usted lo describe: un deployment por cliente. El tenant se resuelve desde el entorno y se aplica con un Global Scope a cada query, para que el aislamiento no dependa de la disciplina. El comando tenant:provision crea una instancia nueva de cliente, migra, seedea e imprime las variables de entorno que hay que setear en el nuevo deployment. Poner un cliente nuevo en marcha es un comando, no un día de proyecto.',
        ],

        [
            'key' => 'streaming',
            'keywords' => ['stream', 'streaming', 'sse', 'tiempo real', 'realtime', 'voice', 'voz', 'latencia', 'responsivo'],
            'answer' => 'El asistente hace streaming de su respuesta por Server-Sent Events, fragmento a fragmento. Mientras tanto ve en vivo qué modelo respondió, cuánto tardó y si vino de caché — la procedencia no se esconde. Esa es la UI en tiempo real que usted menciona como nice to have. En interfaces de voz sería el que estoy aprendiendo: conozco el terreno del streaming, no el del audio.',
        ],

        [
            'key' => 'experience',
            'keywords' => ['experiencia', 'proyecto', 'ptd', 'talento', 'cincinnatus', 'trabajo', 'curriculum', 'cv', 'lead', 'equipo'],
            'answer' => 'Mi rol más importante fue Lead Tech en PTD-Talento, el marketplace de talento del Cincinnatus Institute of Craftsmanship: equipo de cuatro, 18 sprints, 74 pull requests e issues mergeados. Concreto, fui responsable de un P1 IDOR en una ruta de favoritos, de hacer idempotentes las migraciones, del audit trail, del rate-limiting anti-spam en el formulario de solicitudes y de mover las subidas a Google Cloud Storage. Además, mi Técnico en curso y el bootcamp full-stack en el Cincinnatus Institute.',
        ],

        [
            'key' => 'german',
            'keywords' => ['aleman', 'alemán', 'idioma', 'idiomas', 'cliente', 'entender'],
            'answer' => 'El alemán no es mi lengua materna. El español sí, y hablo inglés en B2. Este sitio está completo en alemán y el asistente con el que está hablando ahora responde en alemán. Escribo alemán de negocios y entiendo texto técnico y discusión de producto sin esfuerzo. Lo que no afirmo: conversaciones fluidas con clientes al nivel que espera un cliente enterprise en la región DACH. Ese es el único requisito de su anuncio que no cumplo por completo, y lo digo ahora en lugar de en la entrevista.',
        ],

        [
            'key' => 'location',
            'keywords' => ['ubicacion', 'ubicación', 'donde', 'dónde', 'vivo', 'vives', 'suiza', 'suizo', 'distancia', 'viaje', 'presencial', 'permiso', 'visa', 'visado', 'pasaporte', 'remoto', 'zona horaria'],
            'answer' => 'Vivo en la República Dominicana, UTC−4, así que no en la región DACH — lo digo de entrada porque usted lo preguntó. Lo que sí aplica: soy ciudadano suizo, así que no hace falta permiso de trabajo ni previsibilidad de visado. El solapamiento con CET/CEST es por la mañana. Para citas presenciales planifico con antelación. Si necesita una entrevista en Suiza o en la región DACH, dígamelo — estoy dispuesto a viajar.',
        ],

        [
            'key' => 'contact',
            'keywords' => ['contacto', 'postulacion', 'postulación', 'carta', 'escribir', 'email', 'correo', 'telefono', 'llamar', 'linkedin', 'github'],
            'answer' => 'La vía directa es un correo a diegosamuel042@gmail.com, o escribir a Daniel Intrinsa a la dirección que me dio — me interesa que le llegue. Como usted responde en una semana, probablemente me contacten en uno o dos días. El código fuente de este sitio es público y la instancia viva corre en Vercel.',
        ],

    ],

    'intents_fallback' => 'Esa pregunta queda fuera de lo que puedo responder con seguridad. Puedo hablar de Laravel, React, TypeScript, features de IA, tests, multi-tenancy, streaming, mi experiencia laboral, mis idiomas y dónde estoy — y de todo lo demás prefiero no decir nada que no pueda demostrar. Para el resto: un correo a diegosamuel042@gmail.com, o el número de su anuncio directamente.',

];
