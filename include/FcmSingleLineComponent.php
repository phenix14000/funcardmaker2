<?php

require_once('include/FcmFuncardComponent.php');

/**
 * Component affichant une ligne de texte (Titre, type, etc.)
 *
 * Parameters : 
 * - x : position x
 * - y : position y
 * - color : text color
 * - size : font size in em basesize
 * - text : text to display
 * - font : font name
 * - align : aligmement : 'left', 'right' or 'center'. Default left.
 */

class FcmSingleLineComponent extends FcmFuncardComponent {
    
    // self::$draw est disponible par héritage
    
    public function __construct($funcard, $priority = 0) {
        parent::__construct($funcard, $priority);
    }
    
    public function apply(){
        //var_dump($this);
        if(empty($this->getParameter('text'))) return;
        
        self::$draw->push();
        
        self::$draw->setFillColor($this->getParameter('color'));
        self::$draw->setFont(self::$fontManager->getFont($this->getParameter('font')));
        self::$draw->setFontSize($this->getFuncard()->fsc($this->getParameter('size')));
        
        $align = $this->getParameter('align');
        if($align != 'left' && $align != 'right' && $align != 'center')
            $align = 'left';
        if($align == 'right')
            self::$draw->setTextAlignment(imagick::ALIGN_RIGHT);
        elseif($align == 'center')
            self::$draw->setTextAlignment(imagick::ALIGN_CENTER);
        
        $this->getFuncard()->getCanvas()->annotateImage(
            self::$draw,
            $this->getFuncard()->xc($this->getParameter('x')),
            $this->getFuncard()->yc($this->getParameter('y')),
            0,
            $this->getParameter('text')
        );
        
        self::$draw->pop();
    }
    
    public function setDefaultParameters(){
        $this->setParameter('color', 'black');
        $this->setParameter('size', 1);
        $this->setParameter('text', '');
        $this->setParameter('font', 'matrix');
        $this->setParameter('align', 'left');
    }
    
    public function configure(){ return false; }
    
}