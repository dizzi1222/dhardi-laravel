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

    'title' => $name.' — Full-Stack-Entwickler: Laravel, React, TypeScript',
    'site_short' => $name,

    'description' => 'Portfolio von '.$name.'. Laravel-13-Backend mit Instanz-Mandantenfähigkeit, React 19 mit TypeScript im Strict-Modus, und ein KI-Assistent mit Retry-, Fallback-, Cache- und Eval-Schicht. Schweizer Staatsbürger, kein Arbeitsbewilligungs-Bedarf.',

];
