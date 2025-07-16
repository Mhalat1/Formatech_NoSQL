<?php
namespace App\Security;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;



class InscriptionVoter extends Voter
//supports et voteOnAttribute sont des fonction symfony avec leurs variables spécifiques
// InscriptionVoter est une classe qui étend Voter, une classe de base pour
// créer des votants personnalisés dans Symfony. Elle est utilisée pour vérifier
{
    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'VALID_SUBSCRIPTION';
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Utilisateur) {
            return false;
        }
        return $user->getDateFinAbonnement() > new \DateTime();
    }
}