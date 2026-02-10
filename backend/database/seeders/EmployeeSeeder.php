<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = Division::all();

        $employees = [
            ['name' => 'Budi Santoso', 'phone' => '081234567001', 'position' => 'Senior Developer'],
            ['name' => 'Siti Rahayu', 'phone' => '081234567002', 'position' => 'Junior Developer'],
            ['name' => 'Ahmad Fauzi', 'phone' => '081234567003', 'position' => 'QA Engineer'],
            ['name' => 'Dewi Lestari', 'phone' => '081234567004', 'position' => 'UI Designer'],
            ['name' => 'Andi Prasetyo', 'phone' => '081234567005', 'position' => 'Backend Developer'],
            ['name' => 'Rina Wati', 'phone' => '081234567006', 'position' => 'Frontend Developer'],
            ['name' => 'Joko Widodo', 'phone' => '081234567007', 'position' => 'Project Manager'],
            ['name' => 'Maya Sari', 'phone' => '081234567008', 'position' => 'Full Stack Developer'],
            ['name' => 'Rudi Hermawan', 'phone' => '081234567009', 'position' => 'DevOps Engineer'],
            ['name' => 'Lina Kusuma', 'phone' => '081234567010', 'position' => 'UX Researcher'],
            ['name' => 'Doni Pratama', 'phone' => '081234567011', 'position' => 'Mobile Developer'],
            ['name' => 'Fitri Handayani', 'phone' => '081234567012', 'position' => 'Tech Lead'],
        ];

        foreach ($employees as $index => $data) {
            Employee::create([
                'image' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . explode(' ', $data['name'])[0],
                'name' => $data['name'],
                'phone' => $data['phone'],
                'division_id' => $divisions[$index % count($divisions)]->id,
                'position' => $data['position'],
            ]);
        }
    }
}
