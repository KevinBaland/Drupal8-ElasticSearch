<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\test_kb;

use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;

/**
 * Description of BatchAddNode
 *
 * @author kevin
 */
class BatchAddNode {

    public static function addNodes($postChunk, &$context) {

        $message = 'Adding Nodes...';
        $results = array();

        //Recuperation des Terms dans un Tableau associatif nom -> id
        $arrayTaxoPoste = BatchAddNode::getTaxonomiePoste();
        
        //Parcour de 10 elements 
        foreach ($postChunk as $element) {

            //Creation d'une node
            $node = Node::create(array(
                  'type' => 'poste',
                  'title' => $element['fields']['libelle_du_site']
            ));
            
            //Set des attributs d'un content type Poste
            $node->set('field_accessibilite_fauteuil_rou', $element['fields']['accessibilite_entree_autonome_en_fauteuil_roulant_possible'] == "Oui" ? true : false);
            $node->set('field_adresse', $element['fields']['adresse']);
            $node->set('field_affranchissement_libre_ser', $element['fields']['affranchissement_libre_service'] == "Oui" ? true : false);
            $node->set('field_bornes_de_rechargement_mon', $element['fields']['bornes_de_rechargement_moneo'] == "Oui" ? true : false);
            $node->set('field_caracteristique_du_site', $element['fields']['caracteristique_du_site']);
            $node->set('field_changeur_de_monnaie', $element['fields']['changeur_de_monnaie'] == "Oui" ? true : false);
            $node->set('field_code_postal', $element['fields']['code_postal']);

            //Vérification du departement dans la taxonomie, si il n'y est pas l'ajoute.
            if (!array_key_exists($element['fields']['dept'], $arrayTaxoPoste)) {
                $idTerm = BatchAddNode::addTaxonomiePoste($element['fields']['dept']);
                $arrayTaxoPoste[$element['fields']['dept']] = $idTerm;
            }

            $node->set('field_dept', $arrayTaxoPoste[$element['fields']['dept']]);
            $node->set('field_distributeur_de_billets', $element['fields']['distributeur_de_billets'] == "Oui" ? true : false);
            $node->set('field_distributeur_de_timbres', $element['fields']['distributeur_de_timbres'] == "Oui" ? true : false);
            $node->set('field_distributeur_pret_a_poster', $element['fields']['distributeur_pret_a_poster'] == "Oui" ? true : false);
            $node->set('field_identifiant', $element['fields']['identifiant']);
            $node->set('field_latitude', $element['fields']['latitude']);
            $node->set('field_libelle_du_site', $element['fields']['libelle_du_site']);
            $node->set('field_localite', $element['fields']['localite']);
            $node->set('field_longitude', $element['fields']['longitude']);
            $node->set('field_numero_de_telephone', $element['fields']['numero_de_telephone']);
            $node->set('field_pays', $element['fields']['pays']);
            $node->set('field_photocopie', $element['fields']['photocopie'] == "Oui" ? true : false);
            $node->set('field_precision_du_geocodage', $element['fields']['precision_du_geocodage']);
            $node->set('field_recordid', $element['field_recordid']);

            //Sauvegarde de la node
            $node->save();
        }
        
        $context['message'] = $message;
        $context['results'] = $results;
    }

    static function addNodeFinishedCallback($success, $results, $operations) {
        // The 'success' parameter means no fatal PHP errors were detected. All
        // other error management should be handled using 'results'.
        if ($success) {
            $message = \Drupal::translation()->formatPlural(
                count($results), 'One post processed.', '@count posts processed.'
            );
        }
        else {
            $message = t('Finished with an error.');
        }
        drupal_set_message($message);
    }

    /**
     * Recuperation des Terms deja existant
     * 
     * @return array Un tableau associatif
     */
    static function getTaxonomiePoste() {
        //Recuperation de la liste des ids des Term correspondant a Departement
        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', "departement");
        $tids = $query->execute();

        //Creation d'un tableau ou l'on va stocker le nom des terms existant associer a leur id
        $arrayTerm = [];

        //Parcour de la liste des ids
        foreach ($tids as $id) {
            //recuperation du Term correspondant selon l'id
            $term = Term::load($id);

            //Modification du tableau associatif
            $arrayTerm[$term->getName()] = $id;
        }

        return $arrayTerm;
    }

    /**
     * Fonction de création d'un Term supplémentaire au vocabulaire.
     * 
     * @param String $name Nom du Term a créer
     * @return String id du Term créé
     */
    static function addTaxonomiePoste($name) {
        $term = Term::create([
              'vid' => 'departement',
              'name' => $name,
        ]);
        $term->save();

        return $term->id();
    }

}
