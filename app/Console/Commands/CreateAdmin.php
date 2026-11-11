<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin {email?} {--name=Pengelola} {--from-env}';
    protected $description = 'Membuat akun pengelola katalog tanpa menaruh kata sandi di kode sumber.';

    public function handle(): int
    {
        $fromEnv = $this->option('from-env');
        $email = $fromEnv ? config('catalog.admin_email') : ($this->argument('email') ?: $this->ask('Email pengelola'));
        $password = $fromEnv ? config('catalog.admin_password') : $this->secret('Kata sandi, minimal 12 karakter');
        $name = $fromEnv ? config('catalog.admin_name') : $this->option('name');

        if ($fromEnv && (! $email || ! $password)) {
            $this->info('ADMIN_EMAIL dan ADMIN_PASSWORD belum diatur. Akun pengelola tidak dibuat.');
            return self::SUCCESS;
        }
        $validator = Validator::make(compact('email','password'), [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:12|max:128',
        ]);
        if ($validator->fails()) {
            $this->error(implode(' ', $validator->errors()->all()));
            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->save();
        $this->info('Akun pengelola berhasil disimpan. Kata sandi tidak ditampilkan.');
        return self::SUCCESS;
    }
}
