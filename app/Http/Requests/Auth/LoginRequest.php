<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Se realizan las validaciones minimas al formulario
        return [
            'tipo_documento' => ['required'],
            'numero_documento' => ['required', 'string', 'max:10'],
            'password' => ['required'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate($request, $deslogear = 0)
    {

        // Se valida el numero de intentos
        $this->ensureIsNotRateLimited();

        // Se captura la informacion
        $tipo_documento = $request->tipo_documento;
        $numero_documento =  $request->numero_documento;
        $password = $request->password;

        // Se valida que el tipo de documento, numero y la contraseña sean correctas

        if (!Auth::attempt(['tipo_documento' => $tipo_documento, 'numero_documento' => $numero_documento, 'password' => $password, 'estado' => 1])) {

            // Se realizan las excepciones
            RateLimiter::hit($this->throttleKey());

            // Se manda el mensaje de error
            throw ValidationException::withMessages([
                'password' => trans('auth.password'),
            ]);
        }

        // Se valida para quitar la session la primera vez
        if ($deslogear == 1) {

            // Se checkea que este iniciada la session
            if (Auth::check()) {

                // Se deslogea al usuario
                Auth::logout();
            }
        }

        // Se limpia el numero de intentos
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        // Se valida cuando se ha superando el maximo de intentos en el login
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {

            // Se retorna
            return;
        }

        // Se registra un nuevo evento bloqueado
        event(new Lockout($this));

        // Se validan las credenciales
        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Se manda la excepcion cuando la contraseña es incorrecta
        throw ValidationException::withMessages([
            'password' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::transliterate(Str::lower($this->input('tipo_documento')) . Str::lower($this->input('numero_documento')) . '|' . $this->ip());
    }
}