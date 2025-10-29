<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

User::firstOrCreate(
  ['email'=>'admin@demo.test'],
  ['name'=>'Admin','password'=>Hash::make('Admin123*')]
);

