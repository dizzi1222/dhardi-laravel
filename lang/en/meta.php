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

    'description' => 'Portfolio of '.$name.'. Laravel 13 backend with instance-per-customer tenancy, React 19 with strict TypeScript, and an AI assistant with a retry, fallback, cache and eval layer. Swiss citizen, no work permit required.',

];
