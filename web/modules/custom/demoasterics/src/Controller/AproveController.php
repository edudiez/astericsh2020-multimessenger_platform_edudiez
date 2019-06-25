<?php

namespace Drupal\demoasterics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class AproveController extends ControllerBase {
     public function aprove($sid) {
        $submission = \Drupal\webform\Entity\WebformSubmission::load($sid);
        $data = $submission->getData();
        $data['scheduled'] = TRUE;
        $submission->setData($data);
        $submission->save();
        
        // ENVIAR MAIL DE APROBACIO //
        if($data['observatory']) {
            // LLISTA DE MAILING A ENVIAR
            $mailing = array();
            // PRIMER USUARI QUE POSEEIX OBSERVATORI
            $connection = \Drupal::database();
            $query = $connection->select('users_field_data', 'u');
            $query->join('user__field_user_observatory','o','o.entity_id = u.uid');
            $result = $query->condition('o.field_user_observatory_target_id',$data['observatory'])
                ->fields('u',array('mail'))
                ->execute()
                ->fetchAll();
            if($result) {
                $mailing[] = $result[0]->mail;
            }
            // DESPRES ELS SUSCRITS
            $query = $connection->select('users_field_data', 'u');
            $query->join('taxonomy_term__field_observatory_suscribers','s','u.uid = s.field_observatory_suscribers_target_id');
            $result = $query->condition('s.entity_id',$data['observatory'])
                ->fields('u',array('mail'))
                ->execute()
                ->fetchAll();
            foreach($result as $mail) {
                $mailing[] = $mail->mail;
            }
            $observatory =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($data['observatory']);
            $mailManager = \Drupal::service('plugin.manager.mail');
            foreach($mailing as $mail) {
                $module = 'demoasterics';
                $key = 'aproved_observation';
                $to = $mail;
                $params['message'] = '<p>A petition of a scheduled observation with data.</p>';
                $params['message'] .= '<p>RA:'.$data['ra'].'</p>';
                $params['message'] .= '<p>DEC:'.$data['dec'].'</p>';
                $params['message'] .= '<p>FROM:'.$data['from'].'</p>';
                $params['message'] .= '<p>TO:'.$data['to'].'</p>';
                $params['message'] .= '<p>Has been aproved</p>';
                $params['observatory'] = $observatory->name->value;
                $langcode = 'en';
                $send = TRUE;
                $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
            }
        }
        
        return new JsonResponse(array('message'=>"The Observation has been scheduled"));
     }
}