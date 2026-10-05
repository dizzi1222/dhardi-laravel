<?php

declare(strict_types=1);

return [

    'brand' => 'DH',
    'language_label' => 'Idioma',
    'skip_to_content' => 'Saltar al contenido',

    'nav' => [
        'fit' => 'Encaje',
        'experience' => 'Experiencia',
        'projects' => 'Proyectos',
        'stack' => 'Stack',
        'assistant' => 'Asistente',
        'contact' => 'Contacto',
    ],

    'cv' => 'Currículum',

    'available' => 'Abierto a nuevos proyectos',

    'sections' => [

        'fit' => [
            'eyebrow' => 'Requisitos',
            'heading' => 'Por qué encajo en esta vacante',
        ],

        'experience' => [
            'eyebrow' => 'Trayectoria',
            'heading' => 'Experiencia',
            'subtitle' => 'Roles, responsabilidad y lo que realmente se entregó.',
            'highlights' => 'Responsabilidad',
            'duration_label' => 'Duración',
            'current' => 'Actualidad',
            'remote' => 'Remoto',
            'months' => 'meses',
            'no_highlights' => 'Sin más detalles registrados.',
        ],

        'projects' => [
            'eyebrow' => 'Trabajo',
            'heading' => 'Proyectos',
            'subtitle' => 'Cada uno con su stack, su estado y la evidencia que sostiene la afirmación.',
            'live' => 'En vivo',
            'source' => 'Código',
            'view' => 'Caso de estudio',
            'problem' => 'Punto de partida',
            'approach' => 'Enfoque',
            'highlights' => 'Resultado',
            'metrics' => 'Métricas',
            'featured' => 'En el foco',
        ],

        'stack' => [
            'eyebrow' => 'Evidencia',
            'heading' => 'Stack con evidencia',
            'subtitle' => 'Cada entrada apunta a un repositorio o a un commit, no a una afirmación.',
        ],

        'skills' => [
            'heading' => 'Tecnologías',
            'subtitle' => 'Con el artefacto al lado que respalda la profundidad.',
        ],

        'certifications' => [
            'eyebrow' => 'Formación',
            'heading' => 'Formación y certificaciones',
            'subtitle' => 'Lo completado y lo que está en curso, separado.',
        ],

        'contact' => [
            'eyebrow' => 'Contacto',
            'heading' => 'Contacto',
            'subtitle' => 'Sin formulario, sin tracking. Directo, como usted lo formuló en la oferta.',
            'email_label' => 'Correo',
            'cv_label' => 'Currículum en PDF',
            'primary_cta' => 'Escribir un correo',
        ],

        'footer' => [
            'built_with' => 'Construido con Laravel 13, Inertia 3, React 19, TypeScript, Tailwind 4 y Pest. Desplegado en Vercel en un contenedor Docker con FrankenPHP.',
            'source' => 'Código de este sitio',
            'rights' => 'Todos los derechos reservados.',
        ],

    ],

    'status' => [
        'shipped' => 'Entregado',
        'in_progress' => 'En curso',
        'archived' => 'Archivado',
    ],

    'cert_status' => [
        'completed' => 'Completado',
        'in_progress' => 'En curso',
    ],

    'profile' => [
        'role' => 'Rol',
        'born' => 'Nacimiento',
    ],

    // Etiquetas del objeto `metrics` de un proyecto. Las claves son slugs
    // estables; el texto se traduce, de modo que el mismo proyecto se renderiza
    // bien en los tres idiomas sin que el fichero de contenido conozca ninguno.
    'metrics' => [
        'languages' => 'Idiomas',
        'backends' => 'Backends',
        'tenancy' => 'Modelo de tenancy',
        'prs' => 'PRs e issues',
        'sprints' => 'Sprints',
        'team' => 'Equipo',
        'epics' => 'Épicas',
        'resources' => 'Recursos REST',
        'ci_stages' => 'Etapas de CI',
        'tables' => 'Tablas',
        'deploy' => 'Entrega',
        'migrations' => 'Migraciones',
        'windows' => 'Ventanas',
        'packages' => 'Paquetes',
        'rebuild' => 'Rebuild',
        'tools' => 'Tools',
        'ssrf' => 'Protección SSRF',
    ],

    'project' => [
        'back' => 'Volver al índice',
        'other' => 'Otros proyectos',
        'not_found' => 'Ese proyecto no existe.',
    ],

    'assistant' => [
        'eyebrow' => 'Demo en vivo',
        'heading' => 'Pregúntele al asistente',
        'subtitle' => 'El asistente responde a partir de los datos de este sitio y siempre dice qué modelo respondió. Corre por la misma capa gateway que un producto en producción: reintentos, circuit breaker, fallback, caché, guardrails.',
        'placeholder' => 'Por ejemplo: ¿Cómo está resuelto el multi-tenancy?',
        'send' => 'Preguntar',
        'sending' => 'Respondiendo …',
        'clear' => 'Nueva conversación',
        'suggestions' => [
            '¿Por qué Laravel y no Node?',
            '¿Cómo funciona la capa de fiabilidad?',
            '¿Cómo se da de alta un cliente nuevo?',
            '¿Hablas alemán?',
        ],
        'meta_provider' => 'Modelo',
        'meta_latency' => 'Duración',
        'meta_cached' => 'de caché',
        'meta_cost' => 'Coste',
        'disclaimer' => 'El asistente puede equivocarse. Se alimenta del contenido de este sitio y no inventa hechos que no estén ahí — pero contraste lo que diga sobre mí con mi repositorio, por favor.',
        'empty' => 'Todavía no se ha hecho ninguna pregunta.',
    ],

    'telemetry' => [
        'eyebrow' => 'Operación',
        'heading' => 'Lo que mide la capa de fiabilidad',
        'subtitle' => 'En vivo desde la tabla llm_runs. Ninguna cifra estimada: cada valor es una consulta sobre los intentos que esta misma aplicación ha registrado.',
        'runs' => 'Intentos',
        'since' => 'desde',
        'metrics' => [
            'success' => 'Tasa de éxito',
            'cache_hit' => 'Aciertos de caché',
            'blocked' => 'Bloqueados por guardrails',
            'fallback' => 'Respondió el fallback',
            'p50' => 'Latencia p50',
            'p95' => 'Latencia p95',
            'max' => 'Latencia máxima',
            'cost_total' => 'Coste total',
            'cost_avg' => 'Coste por intento',
        ],
        'evals' => [
            'heading' => 'Golden dataset',
            'note' => 'Se ejecuta periódicamente contra la cadena viva. Un cambio de prompt o de modelo que altere las respuestas esperadas aparece aquí, y no primero en una conversación con un cliente.',
            'cases' => 'Casos',
            'measured' => 'medidos',
            'pass_rate' => 'Tasa de aprobación',
            'last_run' => 'Última ejecución',
        ],
        'empty' => 'Todavía no hay datos. Esta tabla se llena en cuanto se usa el asistente.',
    ],

    'common' => [
        'back' => 'Volver',
        'close' => 'Cerrar',
        'loading' => 'Cargando …',
        'retry' => 'Reintentar',
        'unknown' => 'Desconocido',
    ],

];
