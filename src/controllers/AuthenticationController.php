<?php
namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\models\UserModel;
use Vanier\Api\Helpers\JWTManager;
/**
 * Controller for Authentication
 */
class AuthenticationController extends BaseController
{
    // HTTP POST: URI /token 
    // Authenticates an API user and generates a JWT token.
    public function handleGetToken(Request $request, Response $response, array $args) {
        $user_data = $request->getParsedBody();
        //var_dump($user_data);exit;
        $user_model = new UserModel();
        $jwtManager = new JWTManager();

        if (empty($user_data)) {
            return $this->prepareResponseAuthentication($response,
                    ['error' => true, 'message' => 'No data was provided in the request.'], 400);
        }
        // The received user credentials.
        $email = $user_data["email"];
        $password = $user_data["password"];
        // Verify if the provided email address is already stored in the DB.
        $db_user = $user_model->verifyEmail($email);
        if (!$db_user) {
            return $this->prepareResponseAuthentication($response,
                    ['error' => true, 'message' => 'The provided email does not match our records.'], 400);
        }
        // Now we verify if the provided passowrd.
        $db_user = $user_model->verifyPassword($email, $password);
        if (!$db_user) {
            return $this->prepareResponseAuthentication($response,
                    ['error' => true, 'message' => 'The provided password was invalid.'], 400);
        }

        // Valid user detected => Now, we generate and return a JWT.
        // Current time stamp * 60 minutes * 60 seconds
        $jwt_user_info = ["id" => $db_user["user_id"], "email" => $db_user["email"]];
        //$expires_in = time() + 60 * 60;
        $expires_in = time() + 60; // Expires in 1 minute.
        $user_jwt = $jwtManager->generateToken($jwt_user_info, $expires_in);
        //--
        $response_data = json_encode([
            'status' => 1,
            'token' => $user_jwt,
            'message' => 'User logged in successfully!',
        ]);
        $response->getBody()->write($response_data);
        return $response->withStatus(HTTP_OK);
    }

    // HTTP POST: URI /account 
    // Creates a new user account.
    public function handleCreateUserAccount(Request $request, Response $response, array $args) {
        $user_data = $request->getParsedBody();
        // Verify if information about the new user to be created was included in the 
        // request.
        if (empty($user_data)) {
            return $this->prepareResponseAuthentication($response,
                    ['error' => true, 'message' => 'No data was provided in the request.'], 400);
        }
        // Data was provided, we attempt to create an account for the user.        
        $user_model = new UserModel();
        $new_user = $user_model->createUser($user_data);
        //--
        if (!$new_user) {
            // Failed to create the new user.
            return $this->prepareResponseAuthentication($response,
                    ['error' => true, 'message' => 'Failed to create the new user.'], 400);
        }
        // The user account has been created successfully.  
        return $this->prepareResponseAuthentication($response,
                ['error' => false, 'message' => 'The new user account has been created successfully!'], 400);
    }
}