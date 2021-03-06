<?php

namespace Drupal\test_kb\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Description of PaginationController
 *
 * @author kevin
 */
class PaginationPosteController extends ControllerBase{
    
    public function pagination($dept,$page){

        //Recuperation de la liste des ids des Term correspondant au departement souhaité
        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', "departement");
        $query->condition('name', $dept);
        $tids = $query->execute();
        
        //Recuperation de la listes des nodes ids.
        $nodeIds = \Drupal::entityQuery('node')
        ->condition('type', 'poste')
        ->range(($page-1)*3,3)
        ->condition('field_dept',$tids)
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
}
