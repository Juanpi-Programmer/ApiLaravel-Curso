<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Clientes;

class ClientesController extends Controller {
    public function index(){
        
        $json = array(
            "status" => "404",
            "detalle" => "registro con errores"
        );

        return json_encode($json, true);
    }

    //Crear un registro
    public function store(Request $request){
        //recoger datos
        $datos = array(
            "nombre"=>$request->input("nombre"),
            "apellido"=>$request->input("apellido"),
            "email"=>$request->input("email")
        );
        if(!empty($datos)){
            //validar datos
            $validator = Validator::make($datos, [
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:clientes'
            ]);
            //si falla la validacion
            if ($validator->fails()) {
                $err = $validator->errors();
                $json = array(
                    "status" => "404",
                    "details" => "Registro no valido",
                    "error" => $err
                );
                return json_encode($json, true);
            }else{
                $cliente = new Clientes();

                $id_cliente = Hash::make(
                    $datos['nombre'].
                    $datos['apellido'].
                    $datos['email']
                );

            $llave_secreta = Hash::make(
                    $datos['email'].
                    $datos['apellido'].
                    $datos['nombre'],
                    ['rounds' => 12]
                );


                $cliente->nombre =  $datos['nombre'];
                $cliente->apellido = $datos['apellido'];
                $cliente->email = $datos['email'];
                $cliente->id_cliente = str_replace('$','-', $id_cliente);
                $cliente->llave_secreta =  str_replace('$','-', $llave_secreta);

                $cliente->save();

                $json = array(
                    "status" => "200",
                    "credentials" => array(
                        "id_cliente: " => str_replace('$','-', $id_cliente),
                        "llave_secreta:" => str_replace('$','-', $llave_secreta)
                    )
                );

                return json_encode($json, true);
            }

        }else{
             $json = array(
                "status" => "404",
                "detalle" => "registro con errores"
            );
        }
    }
}
