<?php
namespace Controllers;

use Framework\Controller;

class Admin extends Controller {
    public function blanck($params=[]) {
        return $this->view("admin.template", $params);
    }
}
