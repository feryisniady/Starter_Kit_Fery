<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DemoController extends BaseController
{
    public function kmWorkflow(): string
    {
        return view('admin/demo/km_workflow');
    }
}
