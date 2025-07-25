<?php


namespace Etq\Restful\Controller\User;

use Etq\Restful\Controller\BaseController;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;
use Etq\Restful\Helpers;
use Firebase\JWT\JWT;
use \Firebase\JWT\Key;

final class Login extends BaseController
{

    private function setPhoneFirstChar(&$user)
    {
        if (!str_starts_with($user->phone, "+")) {
            $user->phone = "+" . $user->phone;
        }
    }
    public function __invoke(Request $request, Response $response): Response
    {
        $input = $this->checkForBody($request);
        $data = json_decode((string) json_encode($input), false);
        if (! isset($data->phone)) {
            throw new Exception('The field "phone" is required.', 400);
        }
        if (! isset($data->password)) {
            throw new Exception('The field "password" is required.', 400);
        }
        $this->setPhoneFirstChar($data);
        $user = $this->container['user_repository']->loginUser($data->phone, $data->password);
        $token = [
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60),
            'data' => [ # Data related to the signer user
                'iD'   => Helpers::isSetKeyFromObjReturnValue($user, 'iD'),
                'phone' => Helpers::isSetKeyFromObjReturnValue($user, 'phone'),
                'userlevelid' => Helpers::isSetKeyFromObjReturnValue($user, 'userlevelid'),
                # userid from the users table
            ],

        ];

        $message = JWT::encode(
            $token,
            $_SERVER['SECRET_KEY'],
            'HS256'
        );

        Helpers::setKeyValueFromObj($user, 'token', $message);
        Helpers::setKeyValueFromObj($user, 'phone', strval(Helpers::isSetKeyFromObjReturnValue($user, 'phone')));
        Helpers::unSetKeyFromObj($user, PASSWORD_FIELD);

        return $this->jsonResponse($response, 'success', $user, 200);
    }
}
