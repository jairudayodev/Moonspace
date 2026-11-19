<!doctype html>

<html lang="{$smarty.const.SYS_LOCALE}">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="generator" content="Moonspace">
    <meta name="version" content="{$system['system_version']}">

    <!-- Title -->
    <title>{$page_title|truncate:70}</title>
    <!-- Title -->

    <!-- Meta -->
    <meta name="description" content="{$page_description|truncate:300}">
    <meta name="keywords" content="{$system['system_keywords']}">
    {if $page_noindex}
        <meta name="robots" content="noindex, follow">
    {else}
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
        {if $page_canonical}
            <link rel="canonical" href="{$page_canonical}">
        {/if}
    {/if}
    <meta name="theme-color" content="#08060d">
    <!-- Meta -->

    <!-- OG-Meta -->
    <meta property="og:type" content="{if $og_type}{$og_type}{else}website{/if}"/>
    <meta property="og:title" content="{$page_title|truncate:70}"/>
    <meta property="og:description" content="{$page_description|truncate:300}"/>
    <meta property="og:site_name" content="{$system['system_title']}"/>
    {if $page_canonical}
        <meta property="og:url" content="{$page_canonical}"/>
    {/if}
    {if $page_locale}
        <meta property="og:locale" content="{$page_locale['og']}"/>
    {/if}
    <meta property="og:image" content="{$page_image}"/>
    <meta property="og:image:alt" content="{$page_title|truncate:70}"/>
    <!-- OG-Meta -->

    <!-- Twitter-Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{$page_title|truncate:70}"/>
    <meta name="twitter:description" content="{$page_description|truncate:300}"/>
    <meta name="twitter:image" content="{$page_image}"/>
    <!-- Twitter-Meta -->

    <!-- Structured data (home do visitante) -->
    {if $page == "index" && !$user->_logged_in}
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@graph": [
                {
                    "@type": "Organization",
                    "@id": "{$system['system_url']}/#organization",
                    "name": "{$system['system_title']}",
                    "url": "{$system['system_url']}/",
                    "logo": "{$system['system_url']}/content/themes/{$system['theme']}/images/favicon.png"
                },
                {
                    "@type": "WebSite",
                    "@id": "{$system['system_url']}/#website",
                    "name": "{$system['system_title']}",
                    "url": "{$system['system_url']}/",
                    "description": {$page_description_json},
                    "inLanguage": "{if $page_locale}{$page_locale['html']}{else}pt-BR{/if}",
                    "publisher": { "@id": "{$system['system_url']}/#organization" }
                }
            ]
        }
        </script>
    {/if}
    <!-- Structured data -->

    <!-- Favicon -->
    {if $system['system_favicon_default']}
        <link rel="shortcut icon" href="{$system['system_url']}/content/themes/{$system['theme']}/images/favicon.png" />
        <link rel="apple-touch-icon" href="{$system['system_url']}/content/themes/{$system['theme']}/images/favicon.png" />
    {elseif $system['system_favicon']}
        <link rel="shortcut icon" href="{$system['system_uploads']}/{$system['system_favicon']}" />
    {/if}
    <!-- Favicon -->

    <!-- Fonts [Montserrat|Roboto|Font-Awesome] -->
    {if $system['language']['dir'] == "LTR"}
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" crossorigin="anonymous" />
    {else}
        <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" crossorigin="anonymous" />
    {/if}
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" crossorigin="anonymous">
    <!-- Fonts [Poppins|Roboto|Font-Awesome] -->
    
    <!-- CSS -->
    {if $system['language']['dir'] == "LTR"}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
        <link rel="stylesheet" type='text/css' href="{$system['system_url']}/content/themes/{$system['theme']}/css/style.min.css">
    {else}
        <link rel="stylesheet" href="https://cdn.rtlcss.com/bootstrap/v4.5.3/css/bootstrap.min.css" integrity="sha384-JvExCACAZcHNJEc7156QaHXTnQL3hQBixvj5RV5buE7vgnNEzzskDtx9NQ4p6BJe" crossorigin="anonymous">
        <link rel="stylesheet" type='text/css' href="{$system['system_url']}/content/themes/{$system['theme']}/css/style.rtl.min.css">
    {/if}
    <!-- Moonspace visual layer (loaded after the theme, before the admin custom CSS) -->
    <link rel="stylesheet" type='text/css' href="{$system['system_url']}/content/themes/{$system['theme']}/css/moonspace.css?v={$system['system_version']}.3">
    <!-- CSS -->

    <!-- CSS Customized -->
    {include file='_head.css.tpl'}
    <!-- CSS Customized -->

    <!-- Header Custom JavaScript -->
    {if $system['custome_js_header']}
        <script>
            {html_entity_decode($system['custome_js_header'], ENT_QUOTES)}
        </script>
    {/if}
    <!-- Header Custom JavaScript -->

</head>