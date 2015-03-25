<?php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class AdvertType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date',      'date')
      		->add('title',     'text')
      		->add('author',    'text')
      		->add('content',   'textarea')
      		//->add('published', 'checkbox', array('required' => false))
      		->add('image', new ImageType(), array('required' => false))
      		
      		->add('categories', 'entity', array(
				  'class'    => 'OCPlatformBundle:Category',
				  'property' => 'name',
				  'multiple' => true,
				))
      		
      		->add('save',      'submit');
      	
      		// On ajoute une fonction qui va �couter un �v�nement
      		
      		$builder->addEventListener(
      		
      				FormEvents::PRE_SET_DATA,    // 1er argument : L'�v�nement qui nous int�resse : ici, PRE_SET_DATA
      		
      				function(FormEvent $event) { // 2e argument : La fonction � ex�cuter lorsque l'�v�nement est d�clench�      		
      					// On r�cup�re notre objet Advert sous-jacent      		
      					$advert = $event->getData();
      		
      					// Cette condition est importante, on en reparle plus loin      		
      					if (null === $advert) {      		
      						return; // On sort de la fonction sans rien faire lorsque $advert vaut null      		
      					}
      		
      					if (!$advert->getPublished() || null === $advert->getId()) {      		
      						// Si l'annonce n'est pas publi�e, ou si elle n'existe pas encore en base (id est null),      		
      						// alors on ajoute le champ published      		
      						$event->getForm()->add('published', 'checkbox', array('required' => false));      		
      					} else {      		
      						// Sinon, on le supprime      		
      						$event->getForm()->remove('published');      		
      					}      		
      				}      		
      		);      		
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'OC\PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oc_platformbundle_advert';
    }
}
