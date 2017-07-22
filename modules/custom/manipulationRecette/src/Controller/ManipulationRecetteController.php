<?php
/**
 * @file
 * Contains \Drupal\manipulationRecette\Controller\ManipulationRecetteController.
 */
namespace Drupal\manipulationRecette\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;


class ManipulationRecetteController extends ControllerBase {
    
    //Affiche une recette
    public function content() {
     
        //Recuperation de la listes des nodes ids
        $nodeIds = \Drupal::entityQuery('node')
        ->condition('type', 'recette')
        ->execute();
        //var_dump($nodeIds);exit;
        
        //Recuperation manuel d'une node a partir de la liste de nodes ids.
        $node = Node::load($nodeIds['3']);
        
        //Recupere la vue de la node selectionnée.
        $render = node_view($node);

        return $render;
    }
    
    //Affichage de toutes les Recettes
    public function view_all() {
     
        //Recuperation de la listes des nodes ids.
        $nodeIds = \Drupal::entityQuery('node')
        ->condition('type', 'recette')
        ->execute();
        
        //Recuperation des nodes correspondant a la liste des nodes ids.
        $nodes = Node::loadMultiple($nodeIds);
        
        //Tableau contenant la vue de chaque recette.
        $render_array = [];
        
        foreach ($nodes as $node) {
            $render_array[] = node_view($node);
        }
        
        return $render_array;
    }
    
    //Affiche de 2 Recettes par page.
    public function page($page){
        
        //Recuperation de la listes des nodes ids.
        $nodeIds = \Drupal::entityQuery('node')
        ->condition('type', 'recette')
        ->range(($page-1)*2,2)
        ->execute();
        
        //Recuperation des nodes correspondant a la liste des nodes ids.
        $nodes = Node::loadMultiple($nodeIds);
        
        //Tableau contenant la vue de chaque recette.
        $render_array = [];
        
        foreach ($nodes as $node) {
            $render_array[] = node_view($node);
        }
        
        return $render_array;
    }
    
    public function update(){
        
        //Recuperation de la listes des nodes ids
        $nodeIds = \Drupal::entityQuery('node')
        ->condition('type', 'recette')
        ->execute();
        
        //Recuperation manuel d'une node a partir de la liste de nodes ids.
        $node = Node::load($nodeIds['3']);
        
        //Modification du champ 'nombre de personne'
        $node->set('field_nombre_de_personne', 4);
        
        //Sauvegarde de la modification
        $node->save();
        
        return node_view($node);
    }
}