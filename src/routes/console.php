<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:crypto')->everyMinute();

