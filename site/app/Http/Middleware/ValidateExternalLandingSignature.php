<?php

namespace App\Http\Middleware;

use App\Models\LandingRequest;
use App\Support\LandingSignature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateExternalLandingSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isUnsignedGatewayReturn($request)) {
            return $next($request);
        }

        $sharedKey = (string) config('services.landing.signing_key');

        if ($sharedKey === '') {
            abort(500, 'Landing signing key is not configured.');
        }

        $signature = (string) $request->query('signature', '');
        $expires = $request->query('expires');

        if ($signature === '' || ! is_numeric($expires)) {
            abort(403);
        }

        if ((int) $expires < now()->timestamp) {
            abort(403);
        }

        if ($request->isMethod('post')) {
            $payloadHash = (string) $request->query('payload_hash', '');
            $payloadRaw = (string) $request->input('payload', '');

            if ($payloadHash === '' || $payloadRaw === '') {
                abort(403);
            }

            if (! hash_equals($payloadHash, hash('sha256', $payloadRaw))) {
                abort(403);
            }
        }

        $expectedSignature = LandingSignature::sign(
            method: $request->method(),
            path: $request->path(),
            query: $request->query(),
            key: $sharedKey,
        );

        if (! hash_equals($expectedSignature, $signature)) {
            abort(403);
        }

        return $next($request);
    }

    protected function isUnsignedGatewayReturn(Request $request): bool
    {
        $requestId = (string) $request->query('request_id', '');

        if ($requestId === '' || $request->query('signature')) {
            return false;
        }

        return LandingRequest::query()->where('request_id', $requestId)->exists();
    }
}