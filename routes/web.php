<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/applications', 'applications')->name('applications');
Volt::route('/application/add', 'application_add')->name('application_add');