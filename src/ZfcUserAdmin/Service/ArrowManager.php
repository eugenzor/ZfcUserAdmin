<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ZfcUserAdmin\Service;

/**
 * Description of ArrowManager
 *
 * @author eugene
 */
class ArrowManager {
    protected $arrows;
    
    function set($name, $value)
    {
        $this->arrows[$name] = $value;
    }
    
    function get($name)
    {
        return isset($this->arrows[$name])?$this->arrows[$name]:null;
    }
}
