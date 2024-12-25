<?php
namespace App\Controllers;

class AuthController {
    public function login($request, $response) {
        $data = $request->getParsedBody();
        // Login logic here
        return $response->withJson(["status" => "success"]);
    }
}
