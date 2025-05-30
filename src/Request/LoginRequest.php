<?php
namespace App\Request;
use Symfony\Component\Validator\Constraints as Assert; 
class LoginRequest
{
    #[Assert\NotBlank()]
    #[Assert\NotNull()]
    #[Assert\Email()]
    public $email;
    #[Assert\NotBlank()]
    #[Assert\NotNull()]
    public $password;

    public static function fromData(array $data): LoginRequest
    {
        $request = new LoginRequest;
        $request->email = $data['email'];
        $request->password = $data['password'];
        return $request;
    }
}