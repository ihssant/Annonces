<?php


namespace OC\PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class Antiflood extends Constraint
{

	public $message = "Vous avez d�j� post� un message il y a moins de 15 secondes, merci d'attendre un peu.";

}