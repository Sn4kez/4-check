<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    public const REQUEST_PARAM_NAME_PAGE = 'page';
    public const REQUEST_PARAM_NAME_DATE_TO = 'end';
    public const REQUEST_PARAM_NAME_DATE_FROM = 'start';

    public const COLUMN_NAME_RESPONSIBLE_FOR_DATE_FILTER = 'executionAt';
}
