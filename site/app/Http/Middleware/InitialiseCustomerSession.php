<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\CustomerGroup;
use Symfony\Component\HttpFoundation\Response;

class InitialiseCustomerSession
{
    /**
     * Set the StorefrontSession customer groups based on the authenticated user.
     *
     * - Guest: default customer group (everyone).
     * - Authenticated with no customer/groups: default customer group.
     * - Authenticated with customer groups assigned: their specific groups.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && is_lunar_user($user)) {
            $customer = $user->latestCustomer();

            if ($customer) {
                $groups = $customer->customerGroups;

                if ($groups->isNotEmpty()) {
                    StorefrontSession::setCustomerGroups($groups);

                    return $next($request);
                }
            }
        }

        // Guest or authenticated user with no specific group assignment.
        StorefrontSession::setCustomerGroup(CustomerGroup::getDefault());

        return $next($request);
    }
}
