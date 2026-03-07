<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/applications', 'applications')->name('applications');
Volt::route('/application/{id}', 'application')->name('application');

Volt::route('/services/{id}', 'services')->name('services');
Volt::route('/service/{id}', 'service')->name('service');