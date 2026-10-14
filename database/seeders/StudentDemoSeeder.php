<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentDemoSeeder extends Seeder
{
    /**
     * Run the database seeds with realistic demonstration students.
     */
    public function run(): void
    {
        $demoStudents = [
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@gmail.com',
                'system' => 'elite',
                'residence' => 'usa',
                'current_station' => 12,
                'completed_stations' => 12,
                'completed_tasks' => 64,
                'progress_percentage' => 100,
                'last_active_at' => now()->subHours(3),
            ],
            [
                'name' => 'Sofía Villarreal',
                'email' => 'sofia.villarreal@hotmail.com',
                'system' => 'elite',
                'residence' => 'usa',
                'current_station' => 9,
                'completed_stations' => 8,
                'completed_tasks' => 45,
                'progress_percentage' => 70,
                'last_active_at' => now()->subHours(18),
            ],
            [
                'name' => 'Alejandro Morales',
                'email' => 'alejandro.morales@outlook.com',
                'system' => 'medium',
                'residence' => 'outside',
                'current_station' => 6,
                'completed_stations' => 5,
                'completed_tasks' => 28,
                'progress_percentage' => 44,
                'last_active_at' => now()->subDays(1),
            ],
            [
                'name' => 'Mariana Cordero',
                'email' => 'mariana.cordero@gmail.com',
                'system' => 'medium',
                'residence' => 'usa',
                'current_station' => 4,
                'completed_stations' => 3,
                'completed_tasks' => 18,
                'progress_percentage' => 28,
                'last_active_at' => now()->subDays(4),
            ],
            [
                'name' => 'Diego Fernández',
                'email' => 'diego.fernandez@yahoo.com',
                'system' => 'elite',
                'residence' => 'outside',
                'current_station' => 3,
                'completed_stations' => 2,
                'completed_tasks' => 13,
                'progress_percentage' => 20,
                'last_active_at' => now()->subDays(5),
            ],
            [
                'name' => 'Lucía Herrera',
                'email' => 'lucia.herrera@gmail.com',
                'system' => 'medium',
                'residence' => 'outside',
                'current_station' => 2,
                'completed_stations' => 1,
                'completed_tasks' => 8,
                'progress_percentage' => 12,
                'last_active_at' => now()->subDays(12),
            ],
            [
                'name' => 'Roberto Garza',
                'email' => 'roberto.garza@hotmail.com',
                'system' => 'medium',
                'residence' => 'usa',
                'current_station' => 1,
                'completed_stations' => 0,
                'completed_tasks' => 2,
                'progress_percentage' => 3,
                'last_active_at' => now()->subDays(25),
            ],
            [
                'name' => 'Valeria Castillo',
                'email' => 'valeria.castillo@gmail.com',
                'system' => 'elite',
                'residence' => 'usa',
                'current_station' => 11,
                'completed_stations' => 10,
                'completed_tasks' => 58,
                'progress_percentage' => 91,
                'last_active_at' => now()->subMinutes(40),
            ],
        ];

        foreach ($demoStudents as $data) {
            $user = User::firstOrCreate(
                ['email' => mb_strtolower($data['email'])],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password123'),
                ]
            );

            Student::updateOrCreate(
                ['email' => mb_strtolower($data['email'])],
                [
                    'user_id' => $user->id,
                    'full_name' => $data['name'],
                    'registration_status' => 'approved',
                    'system' => $data['system'],
                    'residence' => $data['residence'],
                    'current_station' => $data['current_station'],
                    'completed_stations' => $data['completed_stations'],
                    'completed_tasks' => $data['completed_tasks'],
                    'progress_percentage' => $data['progress_percentage'],
                    'last_active_at' => $data['last_active_at'],
                ]
            );
        }
    }
}
