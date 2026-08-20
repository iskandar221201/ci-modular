<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $users = auth()->getProvider();

        // Check if admin already exists to prevent duplicate error
        if ($users->findByCredentials(['email' => 'admin@example.com'])) {
            echo "Admin user already exists.\n";
            return;
        }

        $user = new User([
            'username' => 'admin',
            'email'    => 'admin@example.com',
            'password' => 'password123',
        ]);

        $users->save($user);
        
        // Get the saved user to add the group
        $user = $users->findById($users->getInsertID());
        
        if ($user) {
            $users->activate($user);
            $user->addGroup('admin');
            echo "Admin user created successfully.\n";
            echo "Email: admin@example.com\n";
            echo "Password: password123\n";
        }
    }
}
