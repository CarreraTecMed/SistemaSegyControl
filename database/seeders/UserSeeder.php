<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'ci' => '2504903',
            'nombres' => 'Lic. Delia',
            'apellidoPaterno' => 'Nina',
            'apellidoMaterno' => 'Huanca de Echegaray',
            'tipo' => 'administrador',
            'estado' => 'activo',
            'imagen' => 'fotoPerfilLicenciada.png',
            'fechaNacimiento' => '2001-05-03',
            'email' => 'deliusnh16@gmail.com',
            'password' => bcrypt('Delia123.'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('users')->insert([
            'ci' => '2682295',
            'nombres' => 'Maria del Carmen',
            'apellidoPaterno' => 'Murillo',
            'apellidoMaterno' => 'de Espinoza',
            'tipo' => 'administrativo',
            'estado' => 'activo',
            'imagen' => 'fotoPerfilCarmencita.png',
            'fechaNacimiento' => '1968-05-10',
            'email' => 'carmen@gmail.com',
            'password' => bcrypt('Carmen123.'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('users')->insert([
            'ci' => '8441651',
            'nombres' => 'Roberto',
            'apellidoPaterno' => 'Ayala',
            'apellidoMaterno' => 'Mamani',
            'tipo' => 'administrativo',
            'estado' => 'activo',
            'imagen' => 'fotoPerfilRobertito.jpg',
            'fechaNacimiento' => '2001-05-03',
            'email' => 'roberto@gmail.com',
            'password' => bcrypt('Roberto123.'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
