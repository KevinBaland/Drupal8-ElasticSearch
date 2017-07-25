<?php

namespace Drupal\test_kb\Form;

use Drupal\Core\Form\FormBase;
use Drupal\elasticsearch_connector\Entity\Cluster;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

class ElasticForm extends FormBase {

    public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state): array {

        //Selection du champ sur lequel on souhaite effectuer la recherche
        $form['selected_field'] = array(
          '#type' => 'select',
          '#title' => 'Field',
          '#options' => array(
            'field_adresse' => 'Adresse',
            'field_code_postal' => 'Code Postal',
            'field_dept' => 'Departement',
            'field_localite' => 'Localite',
            'field_numero_de_telephone' => 'Numero de Telephone',
          ),
          '#default_value' => 'field_adresse',
          '#description' => 'Selection d\'un champ de recherche',
        );

        //Selection du type d'operation
        $form['selected_operation'] = array(
          '#type' => 'select',
          '#title' => 'Operation',
          '#options' => array(
            'match' => 'match',
            'prefix' => 'prefix',
            'term' => 'term',
            'regexp' => 'regexp',
          ),
          '#default_value' => 0,
          '#description' => 'Selection d\'une operations',
        );

        //Champ de recherche
        $form['text_search'] = array(
          '#type' => 'textfield',
          '#markup' => t('Use this form to send a message to an e-mail address. No spamming!'),
        );

        //Button submit
        $form['valider'] = array(
          '#type' => 'submit',
          '#value' => t('Send'),
        );

        //Zone d'affichage des resultats
        $form['results'] = array(
          '#type' => 'container',
        );

        //Affichage des resultats
        if ($results = $form_state->get('results')) {
            $form['results'][] = $results;
        }

        return $form;
    }

    public function getFormId(): string {
        return "formulaire_elasticsearch";
    }

    public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
        parent::validateForm($form, $form_state);

        //Validation des possibles requetes sur le champ code postal
        if ($form_state->getValue('selected_field') == 'field_code_postal') {
            if (!is_numeric($form_state->getValue('text_search'))) {
                $form_state->setErrorByName('text_search', 'Le code postal doit être un entier');
            }

            if (in_array($form_state->getValue('selected_operation'), ['prefix', 'regexp'])) {
                $form_state->setErrorByName('selected_operation', 'Impossible d\'effectuer cette opération sur un Integer');
            }
        }

        //Validation des possibles requetes sur le champ departement
        if ($form_state->getValue('selected_field') == 'field_dept') {
            if (in_array($form_state->getValue('selected_operation'), ['prefix', 'regexp'])) {
                $form_state->setErrorByName('selected_operation', 'Impossible d\'effectuer cette opération sur un Integer');
            }

            if (!is_numeric($form_state->getValue('text_search'))) {
                $form_state->setErrorByName('text_search', 'Le Departement doit être un entier');
            }
            else {
                //Requete pour recuperer touts les Terms du vocabulaire departement
                $query = \Drupal::entityQuery('taxonomy_term');
                $query->condition('vid', "departement");
                $query->condition('name', $form_state->getValue('text_search'));
                $tids = $query->execute();

                if (empty($tids)) {
                    $form_state->setErrorByName('text_search', 'Le Departement demande n\'existe pas');
                }
            }
        }

        //Validation des possibles requetes sur le champ numero de telephone
        if ($form_state->getValue('selected_field') == 'field_numero_de_telephone') {
            if (in_array($form_state->getValue('selected_operation'), ['prefix', 'regexp'])) {
                $form_state->setErrorByName('selected_operation', 'Impossible d\'effectuer cette opération sur un Integer');
            }

            if (!is_numeric($form_state->getValue('text_search'))) {
                $form_state->setErrorByName('text_search', 'Le numero de telephone doit être un entier');
            }
        }
    }

    public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {

        //Recuperation du cluster
        $elasticsearchCluster = Cluster::load('test_cluster');

        //On appel un service afin d'avoir une conexion avec elasticsearch
        $clientManager = \Drupal::service('elasticsearch_connector.client_manager');

        //on attribut notre cluster a la conexion que l'on souhaite
        $elasticsearchClient = $clientManager->getClientForCluster($elasticsearchCluster);

        //Petit changement afin de recuperer l'id du departement et non la valeur pour effectuer une requete
        $message = $form_state->getValue('text_search');
        if ($form_state->getValue('selected_field') == 'field_dept') {

            //Requete pour recuperer touts les Terms du vocabulaire departement
            $query = \Drupal::entityQuery('taxonomy_term');
            $query->condition('vid', "departement");
            $query->condition('name', $message);
            $tids = $query->execute();

            $message = reset($tids);
        }

        //Creation de la requete 
        $search = array(
          'index' => 'elasticsearch_index_drupal_search_index',
          'type' => 'search_index',
          'body' => [
            'query' => [
              $form_state->getValue('selected_operation') => [
                $form_state->getValue('selected_field') =>
                $message
              ]
            ]
          ]
        );

        //Requete a ElasticSearch
        $resultSearch = $elasticsearchClient->search($search);

        $nids = [];

        //Recuperation d'un tableau de nids du resultat
        foreach ($resultSearch->getRawResponse()['hits']['hits'] as $tab) {
            $nids[] = $tab['_source']['nid'][0];
        }

        //Chargement des nodes
        $nodes = Node::loadMultiple($nids);

        //Recuperation de chaque vue
        $render_array = [];
        foreach ($nodes as $node) {
            $render_array[] = node_view($node);
        }

        //Attribution des vues a la div result du formulaire
        $form_state->set('results', $render_array);

        //On demande au formulaire de se reconstruire afin d'afficher les vues ajoutees
        $form_state->setRebuild(TRUE);
    }

}
