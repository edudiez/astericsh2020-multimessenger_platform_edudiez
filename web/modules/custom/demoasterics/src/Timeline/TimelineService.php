<?php

namespace Drupal\demoasterics\Timeline;

class TimelineService {
    
    public function __construct() {}
    public function getTimeline($tid = NULL) {
        $timeline = array();
        if($tid) {
            $timeline['#markup'] = '<div id="timeline">'. $this->getObservatoryTimeline($tid).$this->getLegend().'</div>';
        } else {
           $timeline['#markup'] = '<div id="timeline">'. $this->randomObservatoriesTimeline().$this->getLegend().'</div>';
        }
        return $timeline;
    }
    
    private function randomObservatoriesTimeline() {
        $vid = 'observatories';
        $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
        $tids = array();
        foreach ($terms as $term) {
            $tids[] = $term->tid;
        }
        $keys = array_rand($tids,2);
        $timeline = '';
        foreach ($keys as $key) {
            $timeline.= $this->getObservatoryTimeline($tids[$key]);
        }
        return $timeline;
    }
    
    private function getObservatoryTimeline($tid) {
        $observatory =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid); 
        $row = '<div class="timeline-row" attr-observatory="'.$tid.'">';
        $row .= '<div class="observatory-name"><div class="value">'.$observatory->name->value.'</div></div>';
        $row .= '<div class="schedule">';
        $init = strtotime('1-1-'.date('Y'));
        $now = strtotime('now');
        $end = strtotime('31-12-'.date('Y'));
        $today = intval(100*($now-$init)/($end-$init));
        $row .= '<div class="observations-wrapper"><table class="wrapper"><tr><td class="past" width="'.$today.'%">'.$this->getScheduledObservations(random_int(1+intval($today/100),1+intval(5*$today/100))).'</td><td class="future" width="'.(100-$today).'%">'.$this->getScheduledObservations(1+random_int(1+intval((100-$today)/100),1+intval(5*((100-$today)/100)))).'</td></tr></table></div>';
        $row .= '<div class="calendar">';
        $row .= '<div class="line"></div>';
        $row .= '<div class="months"><div class="month">January</div><div class="month">February</div><div class="month">March</div><div class="month">April</div><div class="month">May</div><div class="month">June</div><div class="month">July</div><div class="month">August</div><div class="month">September</div><div class="month">October</div><div class="month">November</div><div class="month">December</div></div>';
        $row .= '</div>';
        $row .= '</div>';
        $row .= '</div>';
        return $row;
    }
    
    private function getScheduledObservations($num = 1) {
        $observation = '<table class="items-wrapper" width="100%" height="35px"><tr>';
        $width = $this->getWidthArray($num);
        for ($i = 1; $i <= $num; $i++) {
            $observation .= '<td width="'.$width[$i-1].'%" height="35px" class="item delta-'.$i.'">';
            $observation .= '<div class="elipse">';
            if(random_int(0,2) > 0) {
                $observation .= $this->getObservation();
            }
            $observation .= '</div>';
            
            $observation .='</td>';
        }
        $observation .= '<tr></table>';
        return $observation;
    }
    
    private function getObservation() {
        $width = $this->getWidthArray(3);
        $observation = '<div class="made">';
        $observation .= '<table class="made-wrapper"><tr><td width="'.intval($width[0]/3).'%"></td>';
        $observation .= '<td class="made-item"><div class="made-left"></div>';
        $observation .= '<table width="'.random_int(50,90).'%" class="made-center"><tr><td></td></tr></table>';
        $observation .= '<div class="made-right"></div></td>';
        $observation .= '<td width="'.intval($width[2]/3).'%"></td></tr></table>';
        $observation .= '</div>';
        return $observation;
    }
    
    private function getLegend() {
        $legend = '<div class="legend">';
        $legend .= '<div class="made">';
        $legend .= '<table class="made-wrapper"><tr>';
        $legend .= '<td class="made-item"><div class="made-left"></div>';
        $legend .= '<table width="100%" class="made-center"><tr><td></td></tr></table>';
        $legend .= '<div class="made-right"></div></td>';
        $legend .= '</tr></table>';
        $legend .= '<label>Scheduled Observation</label>';
        $legend .= '</div>';
        $legend .= '<div class="made"><table class="made-wrapper"><tr><td><div class="elipse"></div></td></tr></table><div class="visibility">Visibility</div></div>';
        $legend.= '</div>';
        return $legend;
    }
    
    private function getWidthArray($num = 1) {
        $width = array();
        if($num == 1) {
            $width[] = 100;
        } else {
            $t = 100;
            $b = intval(100/$num);
            $v = intval($b/2);
            $r = random_int(-$v,$v);
            $w = $b+$r;
            for ($i = 1; $i <= $num; $i++) {
                if($i < $num) {
                    $width[] = $w;    
                    $t = $t-$w;
                    $b = intval($t/($num-$i));
                    $v = intval($b/2);
                    $r = random_int(-$v,$v);
                    $w = $b+$r;
                } else {
                    $width[] = $t;    
                }
            }
        }
        return $width;
    }
    
}