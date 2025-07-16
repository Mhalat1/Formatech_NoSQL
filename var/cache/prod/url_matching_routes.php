<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
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
                .'|/in(?'
                    .'|foutilisateur/([^/]++)/liste(*:41)'
                    .'|vitation/accept/([^/]++)(*:72)'
                    .'|stitution/([^/]++)/modifier(*:106)'
                .')'
                .'|/utilisateur(?'
                    .'|/([^/]++)/mod(?'
                        .'|ifier(?'
                            .'|\\-roles(*:161)'
                            .'|commentaire(*:180)'
                        .')'
                        .'|ulecommentaire(*:203)'
                    .')'
                    .'|sessionmodule/modifier/([^/]++)(*:243)'
                .')'
                .'|/exporter/([^/]++)/pdf(*:274)'
                .'|/session(?'
                    .'|module/modifier/([^/]++)(*:317)'
                    .'|/([^/]++)/modifier(*:343)'
                .')'
                .'|/module/([^/]++)/modifier(*:377)'
                .'|/verfication/([^/]++)/([^/]++)(*:415)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        41 => [[['_route' => 'utilisateur_liste', '_controller' => 'App\\Controller\\InstitutionController::liste'], ['id'], ['GET' => 0], null, false, false, null]],
        72 => [[['_route' => 'app_invitation_accept', '_controller' => 'App\\Controller\\InvitationController::acceptInvitation'], ['token'], null, null, false, true, null]],
        106 => [[['_route' => 'institution_modifier', '_controller' => 'App\\Controller\\SessionModuleController::modifierInstitution'], ['id'], null, null, false, false, null]],
        161 => [[['_route' => 'utilisateur_modifier_roles', '_controller' => 'App\\Controller\\InstitutionController::modifierRoles'], ['id'], null, null, false, false, null]],
        180 => [[['_route' => 'utilisateur_modifier', '_controller' => 'App\\Controller\\InstitutionController::modifier'], ['id'], null, null, false, false, null]],
        203 => [[['_route' => 'module_commentaire', '_controller' => 'App\\Controller\\InstitutionController::modifiermodulecommentaire'], ['id'], null, null, false, false, null]],
        243 => [[['_route' => 'modifier_utilisateur_institution_session_module', '_controller' => 'App\\Controller\\SessionModuleController::modifierUtilisateurInstitutionSessionModule'], ['id'], null, null, false, true, null]],
        274 => [[['_route' => 'app_exporter_pdf', '_controller' => 'App\\Controller\\PDF_Envoie_Mail::handlePdfRequest'], ['id'], ['POST' => 0], null, false, false, null]],
        317 => [[['_route' => 'modifier_session_module', '_controller' => 'App\\Controller\\SessionModuleController::modifierSessionModule'], ['id'], null, null, false, true, null]],
        343 => [[['_route' => 'session_modifier', '_controller' => 'App\\Controller\\SessionModuleController::modifierSession'], ['id'], null, null, false, false, null]],
        377 => [[['_route' => 'module_modifier', '_controller' => 'App\\Controller\\SessionModuleController::modifierModule'], ['id'], null, null, false, false, null]],
        415 => [
            [['_route' => 'paiement_abonnement', '_controller' => 'App\\Controller\\StripeController::verfication'], ['prix_id', 'nom'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
