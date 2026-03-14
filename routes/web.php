<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'menu')->name('menu');
Volt::route('/application/{id}', 'application')->name('application');
Volt::route('/log/{id}', 'log-viewer')->name('log');
