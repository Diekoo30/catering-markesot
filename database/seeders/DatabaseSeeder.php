<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,   // 1 akun admin default
            SettingSeeder::class,     // Semua pengaturan sistem
            CategorySeeder::class,    // 5 kategori menu
        ]);

        $this->command->info(str_repeat('-', 50));
        $this->command->info('
    __  ___           __                     __ 
   /  |/  /___ ______/ /_____  _________  / /_
  / /|_/ / __ `/ ___/ //_/ _ \/ ___/ __ \/ __/
 / /  / / /_/ / /  / ,< /  __(__  ) /_/ / /_  
/_/  /_/\__,_/_/  /_/|_|\___/____/\____/\__/  
        ');
        $this->command->info(str_repeat('-', 50));
        $this->command->info('Seeding berhasil! Berikut detail login admin Anda:');
        $this->command->info('Email    : admin@gmail.com');
        $this->command->info('Password : admin123');
        $this->command->info(str_repeat('-', 50));
        $this->command->info('Silakan segera login dan ganti password Anda!');
        $this->command->info(str_repeat('-', 50));
    }
}
