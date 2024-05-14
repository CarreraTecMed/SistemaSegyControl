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
            'ci' => '4787478',
            'nombres' => 'Roberto',
            'apellidoPaterno' => 'Ayala',
            'apellidoMaterno' => 'Mamani',
            'tipo' => 'administrativo',
            'estado' => 'activo',
            'imagen' => 'fotoperfilRobertito.jpg',
            'fechaNacimiento' => '2001-05-03',
            'email' => 'roberto@gmail.com',
            'password' => bcrypt('Roberto123.'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('users')->insert([
            'ci' => '6765799',
            'nombres' => 'Walter',
            'apellidoPaterno' => 'Bustillos',
            'apellidoMaterno' => 'Mamani',
            'tipo' => 'administrativo',
            'estado' => 'activo',
            'imagen' => 'fotoperfilWillis.jpg',
            'fechaNacimiento' => '1986-04-26',
            'email' => 'willis@gmail.com',
            'password' => bcrypt('Willis123.'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('users')->insert([
            'ci' => '5974236',
            'nombres' => 'Simon',
            'apellidoPaterno' => 'Pacosillo',
            'apellidoMaterno' => 'Mamani',
            'tipo' => 'administrativo',
            'estado' => 'activo',
            'imagen' => 'fotoperfilSimon.jpg',
            'fechaNacimiento' => '1983-03-24',
            'email' => 'simon@gmail.com',
            'password' => bcrypt('Simon123.'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
