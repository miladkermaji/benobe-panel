<?php

namespace App\Http\Controllers\Mc;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\HasSelectedClinic;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
    use HasSelectedClinic;

}
