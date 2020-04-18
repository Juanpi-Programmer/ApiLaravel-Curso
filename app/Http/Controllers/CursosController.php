<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cursos;
use App\Clientes;

class CursosController extends Controller {

    //Mostrar todos los registros

    public function index( Request $request ) {

        $token = $request->header( 'Authorization' );
        $clientes = Clientes::all();
        $json = array();

        foreach ( $clientes as $key => $value ) {
            if ( 'Basic '.base64_encode( $value['id_cliente'].':'. $value['llave_secreta'] ) == $token ) {

                $cursos = Cursos::all();

                if ( !empty( $cursos ) ) {
                    $json = array(
                        'status' => 200,
                        'statusText' => 'ok',
                        'total_registros' => count( $cursos ),
                        'detalles' => $cursos
                    );
                } else {
                    $json = array(
                        'status' => 404,
                        'statusText' => 'err',
                        'detalles' => 'no hay cursos'
                    );
                }
            }else{
                $json = array(
                    'status' => 404,
                    'detalles' => 'Upss, No esta autorizado para esta peticion'
                );
            }
        }

        return json_encode($json, true);
   
    }
}
