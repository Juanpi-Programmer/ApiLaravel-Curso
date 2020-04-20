<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Cursos;
use App\Clientes;
use Illuminate\Support\Facades\Validator;


class CursosController extends Controller {

    //Mostrar todos los registros

    public function index( Request $request ) {

        $token = $request->header( 'Authorization' );
        $clientes = Clientes::all();
        $json = array();

        foreach ( $clientes as $key => $value ) {
            if ( 'Basic '.base64_encode( $value['id_cliente'].':'. $value['llave_secreta'] ) == $token ) {

                // $cursos = Cursos::all();

                if(isset($_GET["page"])){
                    $cursos = DB::table('cursos')
                    ->join('clientes', 'cursos.id_creador', '=', 'clientes.id')
                    ->select('cursos.id', 'cursos.titulo', 'cursos.descripcion', 'cursos.instructor',
                        'cursos.imagen', 'cursos.id_creador', 'clientes.nombre', 'clientes.apellido')
                    ->paginate(10);
                }else{
                   $cursos = DB::table('cursos')
                    ->join('clientes', 'cursos.id_creador', '=', 'clientes.id')
                    ->select('cursos.id', 'cursos.titulo', 'cursos.descripcion', 'cursos.instructor',
                        'cursos.imagen', 'cursos.id_creador', 'clientes.nombre', 'clientes.apellido')
                    ->get();
                }
               
               

                if ( count( $cursos ) > 0 ) {
                    $json = array(
                        'status' => 200,
                        'statusText' => 'ok',
                        'total_registros' => count( $cursos ),
                        'detalles' => $cursos
                    );

                    return json_encode( $json, true );

                } else {
                    $json = array(
                        'status' => 404,
                        'statusText' => 'err',
                        'detalles' => 'no hay cursos'
                    );

                }
            } else {
                $json = array(
                    'status' => 404,
                    'detalles' => 'Upss, No esta autorizado para esta peticion'
                );
            }
        }
        return json_encode( $json, true );

    }

    //Crear un registro

    public function store( Request $request ) {
        $token = $request->header( 'Authorization' );
        $clientes = Clientes::all();
        $json = array();

        foreach ( $clientes as $key => $value ) {
            if ( 'Basic '.base64_encode( $value['id_cliente'].':'. $value['llave_secreta'] ) == $token ) {
                //Recoger datos
                $datos = array(
                    'titulo'=>$request->input( 'titulo' ),
                    'descripcion'=>$request->input( 'descripcion' ),
                    'instructor'=>$request->input( 'instructor' ),
                    'imagen'=>$request->input( 'imagen' ),
                    'precio'=>$request->input( 'precio' )
                );
                if ( !empty( $datos ) ) {
                    //Validar los datos
                    $validar = Validator::make( $datos, [
                        'titulo' => 'required|string|max:255|unique:cursos',
                        'descripcion' => 'required|string|max:255|unique:cursos',
                        'instructor' => 'required|string|max:255',
                        'imagen' => 'required|string|max:255|unique:cursos',
                        'precio' => 'required|numeric',
                    ] );

                    //si falla la validacion
                    if ( $validar->fails() ) {
                        $json = array(
                            'status' => 404,
                            'detalle' => 'Registro con errores, vea si estan bien los datos',
                            "error" => $validar->errors()
                        );

                        return json_encode( $json, true );
                    } else {
                        $cursos = new Cursos();
                        $cursos->titulo = $datos['titulo'];
                        $cursos->descripcion = $datos['descripcion'];
                        $cursos->instructor = $datos['instructor'];
                        $cursos->imagen = $datos['imagen'];
                        $cursos->precio = $datos['precio'];
                        $cursos->id_creador = $value['id'];

                        $cursos->save();

                        $json = array(
                            'status' => 200,
                            'statusText' => 'ok',
                            'detalle' => 'Creado correctamente',
                            'data' => $cursos
                        );
                        return json_encode( $json, true );

                    }
                } else {
                    $json = array(
                        'status' => 404,
                        'datalle' =>'Los registros no pueden estar vacios'
                    );
                }
            }
        }
    }

    //Tomar un registro
    public function show($id, Request $request){
        $token = $request->header( 'Authorization' );
        $clientes = Clientes::all();
        $json = array();

        foreach ( $clientes as $key => $value ) {
            if ( 'Basic '.base64_encode( $value['id_cliente'].':'. $value['llave_secreta'] ) == $token ) {
                $curso = Cursos::where("id", $id)->get();

                if (count($curso) > 0)  {

                    $json = array(
                        'status' => 200,
                        'statusText' => 'ok',
                        'detalles' => $curso
                    );
                } else {
                    $json = array(
                        'status' => 404,
                        'statusText' => 'err',
                        'detalles' => 'no existe el curso'
                    );
                }
            } else {
                $json = array(
                    'status' => 404,
                    'detalles' => 'Upss, No esta autorizado para esta peticion'
                );
            }
        }

        return json_encode( $json, true );
    }

    //Editar un registro

    public function update( $id, Request $request ) {
        $token = $request->header( 'Authorization' );
        $clientes = Clientes::all();
        $json = array();

        foreach ( $clientes as $key => $value ) {
            if ( 'Basic '.base64_encode( $value['id_cliente'].':'. $value['llave_secreta'] ) == $token ) {
                //Recoger datos
                $datos = array(
                    'titulo'=>$request->input( 'titulo' ),
                    'descripcion'=>$request->input( 'descripcion' ),
                    'instructor'=>$request->input( 'instructor' ),
                    'imagen'=>$request->input( 'imagen' ),
                    'precio'=>$request->input( 'precio' )
                );
                if ( !empty( $datos )) {
                    //Validar los datos
                    $validar = Validator::make( $datos, [
                        'titulo' => 'required|string|max:255',
                        'descripcion' => 'required|string|max:255',
                        'instructor' => 'required|string|max:255',
                        'imagen' => 'required|string|max:255',
                        'precio' => 'required|numeric',
                    ] );

                    //si falla la validacion
                    if ( $validar->fails() ) {
                        $json = array(
                            'status' => 404,
                            'detalle' => 'Registro con errores, vea si estan bien los datos'
                        );

                        return json_encode( $json, true );
                    } else {
                        //me traigo el curso
                        $traer_curso = Cursos::where("id", $id)->get();
                        // y me fijo si el id de cleintes es igual al id del creador
                        if($value["id"] == $traer_curso[0]["id_creador"]){

                            $datos = array(
                                'titulo'=>$datos["titulo"],
                                'descripcion'=>$datos["descripcion"],
                                'instructor'=>$datos["instructor"],
                                'imagen'=>$datos["imagen"],
                                'precio'=>$datos["precio"]
                            );

                            $cursos = Cursos::where("id", $id)->update($datos);
                            $json = array(
                                'status' => 200,
                                'statusText' => 'ok',
                                'detalle' => 'Actualizado correctamente',
                            );
                            return json_encode( $json, true );
                        }else{
                            $json = array(
                                'status' => 404,
                                'datalle' =>'No esta autorizado para modificar este curso'
                            );
                            return json_encode( $json, true );
                        }
                        

                    }
                } else {
                    $json = array(
                        'status' => 404,
                        'datalle' =>'Los registros no pueden estar vacios'
                    );
                }
            }
        }
    }

    //Eliminar un registro

    public function destroy( $id, Request $request){
        $token = $request->header( 'Authorization' );
        $clientes = Clientes::all();
        $json = array();

        foreach ( $clientes as $key => $value ) {
            if ( 'Basic '.base64_encode( $value['id_cliente'].':'. $value['llave_secreta'] ) == $token ) {
                
                $curso = Cursos::where("id", $id)->get();
                if(count($curso) > 0){

                    $curso = Cursos::where("id", $id)->delete();

                    $json = array(
                        'status' => 200,
                        'statusText' => "ok",
                        'detalle' => 'Borrado correctamente'
                    );

                    //Si quiero que el user que lo creo peda eliminar
                    // if($value["id"] == $curso[0]["id_creador"]){
                    //     $curso = Cursos::where("id", $id)->delete();

                    //     $json = array(
                    //         'status' => 200,
                    //         'statusText' => "ok",
                    //         'detalle' => 'Borrado correctamente'
                    //     );

                    // }
                   
                }else{
                    $json = array(
                        'status' => 404,
                        'detalle' => 'El curso que desea borrar no existe'
                    );
                }
            }
        }

        return json_encode($json, true);
    }
}
