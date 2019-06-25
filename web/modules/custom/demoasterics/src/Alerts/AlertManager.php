<?php

namespace Drupal\demoasterics\Alerts;

use Drupal\webform\Entity\WebformSubmission;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\Entity\User;

class AlertManager {
    
    private $user;
    private $suscribed = array();
    private $alerts = array();
    
    public function __construct() {
        $this->user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
        $connection = \Drupal::database();
        $alerts = array();
        // OBSERVATORI PROPI
        $ovid = (isset($this->user->field_user_observatory->getValue()[0])) ? $this->user->field_user_observatory->getValue()[0]['target_id'] : null;
        if($ovid) {
                //*** OBSERVACIONS DEMANDADES ***//
                $query = $connection->select('webform_submission_data', 'o');
                $query->join('webform_submission','s','s.sid=o.sid');
                $result = $query->condition('o.webform_id','observacion')
                                ->condition('o.name','observatory')
                                ->condition('o.value',$ovid)
                                ->fields('o',['sid'])
                                ->fields('s',['created'])
                                ->execute()
                                ->fetchAll();
                foreach($result as $petition) {
                    $alerts[$petition->created] = array('type' => 'petition', 'sid' => $petition->sid);
                }
            }
        // OBSERVATORIS SUSCRITS
        $query = $connection->select('taxonomy_term_data', 't');
        $query->join('taxonomy_term__field_observatory_suscribers', 'subscr', 'subscr.entity_id = t.tid');
        $this->suscribed = $query->condition('t.vid','observatories')
                        ->condition('subscr.field_observatory_suscribers_target_id',$this->user->id())
                        ->fields('t', ['tid'])
                        ->execute()
                        ->fetchAll();
        if(!empty($this->suscribed)) {
            foreach($this->suscribed as $observatory) {
                //*** OBSERVACIONS DEMANDADES ***//
                $query = $connection->select('webform_submission_data', 'o');
                $query->join('webform_submission','s','s.sid=o.sid');
                $result = $query->condition('o.webform_id','observacion')
                                ->condition('o.name','observatory')
                                ->condition('o.value',$observatory->tid)
                                ->fields('o',['sid'])
                                ->fields('s',['created'])
                                ->execute()
                                ->fetchAll();
                foreach($result as $petition) {
                    $alerts[$petition->created] = array('type' => 'petition', 'sid' => $petition->sid);
                }
                //*** EVENTS CREATS ***//
                $query = $connection->select('node_field_data','n');
                $query->join('node__field_event_category_n','obs','obs.entity_id=n.nid');
                $result = $query->condition('n.type','event')
                                ->condition('obs.field_event_category_n_target_id',$observatory->tid)
                                ->fields('n',['nid','created'])
                                ->execute()
                                ->fetchAll();
                 foreach($result as $event) {
                    $alerts[$event->created] = array('type' => 'event', 'nid' => $event->nid);
                }
            }
            // LES ULTIMES 12 NOTIFICACIONS //
            
        }
        krsort($alerts);
        $this->alerts = array_slice($alerts,0,6);
    }
    
    public function isSuscribed() {
        return count($this->alerts);
    }
    
    public function getCountAlerts() {
        return count($this->alerts);
    }
    
    public function notifications() {
        
        $markup = '<div class="notifications"><ul>';
        foreach($this->alerts as $alert) {
            switch ($alert['type']) {
                case 'petition':
                    $webform_submission = WebformSubmission::load($alert['sid']);
                    $observatory =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($webform_submission->getElementData('observatory'));
                    $image = null;
                    if(isset($observatory->field_observatory_fotografia->entity)) {
                        $image = file_url_transform_relative(ImageStyle::load('notify')->buildUrl($observatory->field_observatory_fotografia->entity->getFileUri()));
                    }
                    $category = $observatory->getName();
                    $account = User::load($webform_submission->get('uid')->getValue()[0]['target_id']);
                    $name = 'Anonymous';
                    if($account) {
                        $name = $account->getUsername();
                    }
                    $data = $webform_submission->getData();
                    if(isset($data['scheduled']) && $data['scheduled']) {
                        $markup.='<li class="aproved">';
                    } else {
                        $markup.='<li class="observatory">';
                    }
                    if($image) {
                        $markup.= '<div class="image"><img src="'.$image.'" alt="notify image"></div>';
                    }
                    $markup.='<div class="box">';
                    $markup.='<div class="category">'.$category.'</div>';
                    if(isset($data['scheduled']) && $data['scheduled']) {
                        $markup.='<div class="title">'.$category.' aproved a coordinated observation</div>';
                    } else {
                        $markup.='<div class="title">'.$name.' sent a request for a coordinated observation</div>';
                    }
                    $markup.='<div class="date">RA:'.$webform_submission->getElementData('ra').' DEC:'.$webform_submission->getElementData('dec').' FROM:'.$webform_submission->getElementData('from').' TO:'.$webform_submission->getElementData('to').'</div>';
                    $markup.='</div></li>';

                    break;
                case 'event':
                    $event = \Drupal::entityTypeManager()->getStorage('node')->load($alert['nid']);
                    $observatory =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($event->get('field_event_category_n')->first()->getValue()['target_id']);
                    $image = file_url_transform_relative(ImageStyle::load('notify')->buildUrl($observatory->field_observatory_fotografia->entity->getFileUri()));
                    $category = $observatory->getName();
                    $title = $event->title->value;
                    $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$alert['nid']);
                    $date = new \DateTime($event->field_event_date_n->value);
                    $date = \Drupal::service('date.formatter')->format($date->getTimestamp(), 'teaser_news');
                     $markup.='<li class="new"><a href="'.$alias.'"><div class="image"><img src="'.$image.'" alt="notify image"></div>';
                    $markup.='<div class="box">';
                    $markup.='<div class="category">'.$category.'</div>';
                    $markup.='<div class="title">'.$title.'</div>';
                    $markup.='<div class="date">'.$date.'</div>';
                    $markup.='</div></a></li>';
                    break;
            }
        }
        $markup.='<ul></div>';
        return $markup;
        /*
        return '<div class="notifications">
                    <ul>
                      <li class="observatory">
                        <div class="image">
                          <img src="/themes/custom/asterics/src/images/notify.jpg" alt="notify image">
                        </div>
                        <div class="box">
                          <div class="category">Monte Palomar Observatory</div>
                          <div class="title">Notification title lorem ipsum</div>
                          <div class="date">14/03/2019 6:10 pm</div>
                        </div>
                      </li>
                      <li class="new">
                        <div class="image">
                          <img src="/themes/custom/asterics/src/images/notify2.jpg" alt="notify image">
                        </div>
                        <div class="box">
                          <div class="category">Monte Palomar Observatory</div>
                          <div class="title">Discovery of Planets Around Stars</div>
                          <div class="date">25/03/2019</div>
                        </div>
                      </li>
                    </ul>
                    <a href="#" class="button">All Alerts</a>
                  </div>';
         * 
         */
    }
    
}