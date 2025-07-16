<?php
namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class AbonnementVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'ABONNEMENT_ACTIF';
    }

   protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
{
    $user = $token->getUser();
    
    if (!$user instanceof Utilisateur) {
        throw new AccessDeniedException('Accès refusé');
    }
    
    if (!$user->verifierSiAbonnementActif()) {
        throw new AccessDeniedException('Abonnement expiré');
    }
    return true;
}
}