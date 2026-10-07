<?php

declare(strict_types=1);

/*
 | Page metadata.
 |
 | The title and the description are the most widely copied surface of a site:
 | browser tab, search result, link preview in Slack or Discord. The display
 | name therefore comes from config rather than being written out here, so the
 | public identity is defined in exactly one place.
 |
 | Set PUBLIC_DISPLAY_NAME to change what a visitor, a crawler or a link
 | preview sees.
 */

$name = config('portfolio.public_identity.display_name');

return [

    'title' => $name.' — Full-Stack: Laravel, React, TypeScript',
    'site_short' => $name,

    'description' => 'Portafolio de '.$name.'. Backend en Laravel 13 con multi-tenancy por instancia, React 19 con TypeScript en modo estricto, y un asistente de IA con capa de reintentos, fallback, caché y evals. Ciudadano suizo, sin necesidad de permiso de trabajo.',

];
