<?php

namespace Drupal\demoasterics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class SuscribeController extends ControllerBase {
    
    public function suscribe(TermInterface $taxonomy_term) {
        
        //* POTSER CONTROLAR AQUI QUE NO ESTIGUI JA SUSCRIT PER SI DE CAS *//
        $taxonomy_term->field_observatory_suscribers[] = \Drupal::currentUser()->id();
        $taxonomy_term->save();
        return new JsonResponse(array('message'=>"You have been subscribed to ".$taxonomy_term->name->value));
    }   
    public function unsuscribe(TermInterface $taxonomy_term) {
        
        $suscribers = $taxonomy_term->get('field_observatory_suscribers')->getValue();
        $key = array_search(\Drupal::currentUser()->id(), array_column($suscribers, 'target_id'));
        $taxonomy_term->get('field_observatory_suscribers')->removeItem($key);
        $taxonomy_term->save();
        
        return new JsonResponse(array('message'=>"You have been unsubscribed from ".$taxonomy_term->name->value));
    }
}