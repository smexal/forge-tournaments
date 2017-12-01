<?php
namespace Forge\Core\App;

if(class_exists('App'))
    return;

use PHPUnit\Framework\TestCase;

class App {
    public $db = null;
    public $eh = null;
    public $vm = null;
    public $cm = null;
    public $mm = null;
    public $tm = null;
    public $com = null;
    public $user = null;
    public $stream = false;
    public $sticky = false;
    public $page = false;
    
    private $prepared = false;
    private $uri_components = false;

    static private $instance = null;
    
    static public function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function setUri($components = array()) {}

    private function managers() {}

    /**
     * Allow the the instantiations of the managers
     * inside a PhpUnit-Test and prevent multiple callings
     * of the method
     */
    public function prepare() {}

    public function run() {}

    public function displayView($view) {}

    private function renderViewInTheme($view) {}

    public function header($view) {}

    public function getFavicon($view) {}

    public function getTitle($view) {
        return "Mock Title";
    }

    public function content($view) {
        return "Mock Content";
    }

    public function render($template_dir, $template_file, $args=array()) {
        return "Mock Render";
    }

    public function redirect($target, $go_back=false) {}

    public function refresh($target, $content, $update=false) {}

    public function updatePart($target, $content) {}

    public function redirectBack() {}

    public function addFootprint($uri_components) {}

    public function addMessage($message, $type="warning") {}

    public function displayMessages() {}

    public function stream($start = false) {}

    public function streamActive() {}

    public function getThemeDirectory() {}

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    private function __construct(){}
    private function __clone(){}
}

