<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('greenhouse:proses-jadwal')->hourly();
