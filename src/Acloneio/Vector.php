<?php
namespace Acloneio;

class Vector {
    public $th, $x, $y, $mag;
    public $normalized;
    
    private function calMag() {
        $this->mag = sqrt( ($this->x)*($this->x) + ($this->y)*($this->y) );
    }
    
    private function calTh() {
        $this->th = atan2($this->y, $this->x);
    }
    
    private function calCartesian() {
        $this->x = ($this->mag*cos($this->th));
        $this->y =  ($this->mag*sin($this->th));
    }
    
   /* public function __construct($dir, $Mag) {
        $this->th = $dir;
        $this->mag = $Mag;
        $this->calCartesian();
    }*

/*  public function __construct($xi, $yi, $xf, $yf){
        $this->x = $xf-$xi; $this->y = $yf-$yi;
        calMag();
        calTh();
    }*/
    public function __construct($x, $y){
        $this->x = $x; $this->y = $y;
        $this->calMag();
        $this->calTh();
    }
  
    public function invert() {
        $this->Scale(-1);
        $this->calMag();
        $this->calTh();
        $this->Normalize();
    }

    private function Normalize() {
        $this->calMag();
        $this->normalized = new Vector($this->x/$this->mag, $this->y/$this->mag);
    }

    public function Scale($c) {
        $this->x = $this->x*$c;
        $this->y = $this->y*$c;
        $this->calMag();
        $this->calTh();
    }
    
    public function getNormalized() {
        $this->Normalize();
        return $this->normalized;
    }

    public function getMag() {
        return $this->mag;
    }
    
    public function add($X, $Y) {
        $this->x += $X;
        $this->y += $Y;
        
        $this->calMag();
        $this->calTh();
    }   
}
