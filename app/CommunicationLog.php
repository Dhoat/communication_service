<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    protected $table = 'communication_log';

    const FAILLED     = 0;
    const SUCCESSFULL = 1;

    const TYEP_EMAIL = 1;
    const TYPE_SMS   = 2;

}
