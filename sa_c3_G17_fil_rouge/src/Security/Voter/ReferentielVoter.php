<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ReferentielVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['EDIT', 'SET', 'VIEW','DEL'])
            && $subject instanceof \App\Entity\Referentiel;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'EDIT':
                return ($user->getRoles()[0] === "ROLE_CM" || $user->getRoles()[0] === "ROLE_FORMATEUR") || $user->getRoles()[0] === "ROLE_ADMIN";
            case 'VIEW':
                return ($user->getRoles()[0] === "ROLE_CM" || $user->getRoles()[0] === "ROLE_FORMATEUR") || $user->getRoles()[0] === "ROLE_ADMIN";
            case 'SET':
                return ($user->getRoles()[0] === "ROLE_CM" || $user->getRoles()[0] === "ROLE_FORMATEUR") || $user->getRoles()[0] === "ROLE_ADMIN";
            case 'DEL':
                return ($user->getRoles()[0] === "ROLE_CM" || $user->getRoles()[0] === "ROLE_FORMATEUR") || $user->getRoles()[0] === "ROLE_ADMIN" || $user->getRoles()[0] === "ROLE_APPRENANT";
        }
        return false;
    }
}
