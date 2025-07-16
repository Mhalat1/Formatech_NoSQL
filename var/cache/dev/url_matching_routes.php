<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/_profiler' => [[['_route' => '_profiler_home', '_controller' => 'web_profiler.controller.profiler::homeAction'], null, null, null, true, false, null]],
        '/_profiler/search' => [[['_route' => '_profiler_search', '_controller' => 'web_profiler.controller.profiler::searchAction'], null, null, null, false, false, null]],
        '/_profiler/search_bar' => [[['_route' => '_profiler_search_bar', '_controller' => 'web_profiler.controller.profiler::searchBarAction'], null, null, null, false, false, null]],
        '/_profiler/phpinfo' => [[['_route' => '_profiler_phpinfo', '_controller' => 'web_profiler.controller.profiler::phpinfoAction'], null, null, null, false, false, null]],
        '/_profiler/xdebug' => [[['_route' => '_profiler_xdebug', '_controller' => 'web_profiler.controller.profiler::xdebugAction'], null, null, null, false, false, null]],
        '/_profiler/open' => [[['_route' => '_profiler_open_file', '_controller' => 'web_profiler.controller.profiler::openAction'], null, null, null, false, false, null]],
        '/' => [[['_route' => 'app_accueil', '_controller' => 'App\\Controller\\AccueilController::index'], null, null, null, false, false, null]],
        '/calendrier' => [[['_route' => 'calendrier', '_controller' => 'App\\Controller\\CalendrierController::voirCalendrier'], null, null, null, false, false, null]],
        '/connexion' => [[['_route' => 'app_connexion', '_controller' => 'App\\Controller\\ConnexionController::connexion'], null, null, null, false, false, null]],
        '/deconnexion' => [[['_route' => 'app_deconnexion', '_controller' => 'App\\Controller\\ConnexionController::deconnexion'], null, null, null, false, false, null]],
        '/inscription' => [[['_route' => 'app_inscription', '_controller' => 'App\\Controller\\InscriptionController::register'], null, null, null, false, false, null]],
        '/infoinstitution' => [[['_route' => 'institution_index_ajouter', '_controller' => 'App\\Controller\\InstitutionController::indexEtAjouter'], null, null, null, false, false, null]],
        '/admin/invite' => [[['_route' => 'app_invite', '_controller' => 'App\\Controller\\InvitationController::invite'], null, null, null, false, false, null]],
        '/envoi-session-pdfs' => [[['_route' => 'envoi_session_pdfs', '_controller' => 'App\\Controller\\PDF_Envoie_Mail::envoiSessionPdfs'], null, ['POST' => 0], null, false, false, null]],
        '/envoi-tout-pdfs' => [[['_route' => 'envoi_tout_pdfs', '_controller' => 'App\\Controller\\PDF_Envoie_Mail::envoiToutPdfs'], null, ['POST' => 0], null, false, false, null]],
        '/infosessionmodule' => [[['_route' => 'app_Session_Module', '_controller' => 'App\\Controller\\SessionModuleController::index'], null, null, null, false, false, null]],
        '/abonnements' => [[['_route' => 'liste_abonnements', '_controller' => 'App\\Controller\\StripeController::listeAbonnements'], null, null, null, false, false, null]],
        '/paiement/reussite' => [[['_route' => 'paiement_reussite', '_controller' => 'App\\Controller\\StripeController::reussite'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_(?'
                    .'|error/(\\d+)(?:\\.([^/]++))?(*:38)'
                    .'|wdt/([^/]++)(*:57)'
                    .'|profiler/(?'
                        .'|font/([^/\\.]++)\\.woff2(*:98)'
                        .'|([^/]++)(?'
                            .'|/(?'
                                .'|search/results(*:134)'
                                .'|router(*:148)'
                                .'|exception(?'
                                    .'|(*:168)'
                                    .'|\\.css(*:181)'
                                .')'
                            .')'
                            .'|(*:191)'
                        .')'
                    .')'
                .')'
                .'|/in(?'
                    .'|foutilisateur/([^/]++)/liste(*:236)'
                    .'|vitation/accept/([^/]++)(*:268)'
                    .'|stitution/([^/]++)/modifier(*:303)'
                .')'
                .'|/utilisateur(?'
                    .'|/([^/]++)/mod(?'
                        .'|ifier(?'
                            .'|\\-roles(*:358)'
                            .'|commentaire(*:377)'
                        .')'
                        .'|ulecommentaire(*:400)'
                    .')'
                    .'|sessionmodule/modifier/([^/]++)(*:440)'
                .')'
                .'|/exporter/([^/]++)/pdf(*:471)'
                .'|/session(?'
                    .'|module/modifier/([^/]++)(*:514)'
                    .'|/([^/]++)/modifier(*:540)'
                .')'
                .'|/module/([^/]++)/modifier(*:574)'
                .'|/verfication/([^/]++)/([^/]++)(*:612)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        38 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        57 => [[['_route' => '_wdt', '_controller' => 'web_profiler.controller.profiler::toolbarAction'], ['token'], null, null, false, true, null]],
        98 => [[['_route' => '_profiler_font', '_controller' => 'web_profiler.controller.profiler::fontAction'], ['fontName'], null, null, false, false, null]],
        134 => [[['_route' => '_profiler_search_results', '_controller' => 'web_profiler.controller.profiler::searchResultsAction'], ['token'], null, null, false, false, null]],
        148 => [[['_route' => '_profiler_router', '_controller' => 'web_profiler.controller.router::panelAction'], ['token'], null, null, false, false, null]],
        168 => [[['_route' => '_profiler_exception', '_controller' => 'web_profiler.controller.exception_panel::body'], ['token'], null, null, false, false, null]],
        181 => [[['_route' => '_profiler_exception_css', '_controller' => 'web_profiler.controller.exception_panel::stylesheet'], ['token'], null, null, false, false, null]],
        191 => [[['_route' => '_profiler', '_controller' => 'web_profiler.controller.profiler::panelAction'], ['token'], null, null, false, true, null]],
        236 => [[['_route' => 'utilisateur_liste', '_controller' => 'App\\Controller\\InstitutionController::liste'], ['id'], ['GET' => 0], null, false, false, null]],
        268 => [[['_route' => 'app_invitation_accept', '_controller' => 'App\\Controller\\InvitationController::acceptInvitation'], ['token'], null, null, false, true, null]],
        303 => [[['_route' => 'institution_modifier', '_controller' => 'App\\Controller\\SessionModuleController::modifierInstitution'], ['id'], null, null, false, false, null]],
        358 => [[['_route' => 'utilisateur_modifier_roles', '_controller' => 'App\\Controller\\InstitutionController::modifierRoles'], ['id'], null, null, false, false, null]],
        377 => [[['_route' => 'utilisateur_modifier', '_controller' => 'App\\Controller\\InstitutionController::modifier'], ['id'], null, null, false, false, null]],
        400 => [[['_route' => 'module_commentaire', '_controller' => 'App\\Controller\\InstitutionController::modifiermodulecommentaire'], ['id'], null, null, false, false, null]],
        440 => [[['_route' => 'modifier_utilisateur_institution_session_module', '_controller' => 'App\\Controller\\SessionModuleController::modifierUtilisateurInstitutionSessionModule'], ['id'], null, null, false, true, null]],
        471 => [[['_route' => 'app_exporter_pdf', '_controller' => 'App\\Controller\\PDF_Envoie_Mail::handlePdfRequest'], ['id'], ['POST' => 0], null, false, false, null]],
        514 => [[['_route' => 'modifier_session_module', '_controller' => 'App\\Controller\\SessionModuleController::modifierSessionModule'], ['id'], null, null, false, true, null]],
        540 => [[['_route' => 'session_modifier', '_controller' => 'App\\Controller\\SessionModuleController::modifierSession'], ['id'], null, null, false, false, null]],
        574 => [[['_route' => 'module_modifier', '_controller' => 'App\\Controller\\SessionModuleController::modifierModule'], ['id'], null, null, false, false, null]],
        612 => [
            [['_route' => 'paiement_abonnement', '_controller' => 'App\\Controller\\StripeController::verfication'], ['prix_id', 'nom'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
