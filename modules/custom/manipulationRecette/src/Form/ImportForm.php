<?php

namespace Drupal\manipulationRecette\Form;

use Drupal\Core\Form\FormBase;

class ImportForm extends FormBase{
    
    public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state): array {
        
        //Button join file
        $form['test_field'] = array(
            '#title' => 'Join file',
            '#type' => 'file',
        );
        
        //Button submit
        $form['valider'] = array(
            '#type' => 'submit',
            '#value' => t('Send'),
        );
        
        return $form;
    }

    public function getFormId(): string {
        return 'test_form';
    }

    public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
        
        //Creation d'un validateur pour verifier que le fichier soit bien un fichier json
        $validators = ['file_validate_extensions' => ['json']];
        
        //Sauvegarde du fichier selectione dans le repertoire temporaire de wamp
        $file = file_save_upload('test_field', $validators, FALSE, 0);
        
        //Si le fichier n'est pas un json, affichage d'une erreur
        if (!$file) {
            drupal_set_message('Le Fichier n\'as pas la bonne extension','error');
            return;
        }
        
        //Recuparation de l'url du fichier
        $filename = $file->getFileUri();
        
        //Attribution du contenu du fichier a la variable contents
        $contents = file_get_contents($filename);
        
        //Affichage du Tableau
        drupal_set_message($contents);
    }

}

