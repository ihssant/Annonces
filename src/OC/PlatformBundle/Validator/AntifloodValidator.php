<?php


namespace OC\PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class AntifloodValidator extends ConstraintValidator
{
	
	public function validate($value, Constraint $constraint)
	{
		// Pour l'instant, on consid�re comme flood tout message de moins de 3 caract�res
		if (strlen($value) < 3) {
			// C'est cette ligne qui d�clenche l'erreur pour le formulaire, avec en argument le message de la contrainte
			$this->context->addViolation($constraint->message);
		}
	}
}