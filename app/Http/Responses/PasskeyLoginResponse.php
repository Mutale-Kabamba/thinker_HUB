<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse as PasskeyLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PasskeyLoginResponse implements PasskeyLoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request): Response
    {
        $redirectUrl = redirect()->intended(route('dashboard', absolute: false))->getTargetUrl();

        if ($request->wantsJson()) {
            return new JsonResponse([
                'redirect' => $redirectUrl,
            ], 200);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
